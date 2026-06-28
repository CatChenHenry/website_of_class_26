// 相册照片拖拽排序模块

function savePhotoOrder(albumId) {
    var grid = document.getElementById('album-photo-grid');
    if (!grid) return;
    var ids = [];
    grid.querySelectorAll('.album-photo-item').forEach(function(item) { ids.push(parseInt(item.dataset.photoId)); });
    fetch('/user/reorderPhotos', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: 'csrf_token=' + encodeURIComponent(document.querySelector('input[name="csrf_token"]')?.value || '') + '&ordered_ids=' + encodeURIComponent(JSON.stringify(ids))
    }).catch(function() {});
}

function initDragSort(albumId) {
    var grid = document.getElementById('album-photo-grid');
    if (!grid) return;

    var dragItem = null, ghost = null, placeholder = null;
    var isDragging = false;
    var startX = 0, startY = 0;
    var offsetX = 60, offsetY = 60;
    var lastTarget = null, lastMove = 0;
    var scrollInterval = null;

    function stopAutoScroll() {
        if (scrollInterval) { clearInterval(scrollInterval); scrollInterval = null; }
    }

    function autoScroll(e) {
        var body = document.getElementById('album-modal-body');
        if (!body || body.scrollHeight <= body.clientHeight) return;
        var rect = body.getBoundingClientRect();
        var edge = 60;
        if (e.clientY < rect.top + edge) {
            if (!scrollInterval) {
                scrollInterval = setInterval(function() {
                    body.scrollTop -= 12;
                    if (body.scrollTop <= 0) stopAutoScroll();
                }, 30);
            }
        } else if (e.clientY > rect.bottom - edge) {
            if (!scrollInterval) {
                scrollInterval = setInterval(function() {
                    body.scrollTop += 12;
                    if (body.scrollTop >= body.scrollHeight - body.clientHeight) stopAutoScroll();
                }, 30);
            }
        } else {
            stopAutoScroll();
        }
    }

    function getInsertPoint(e) {
        var style = getComputedStyle(grid);
        var cols = style.gridTemplateColumns.split(' ').filter(Boolean).length;
        if (!cols) return null;
        var colW = parseFloat(style.gridTemplateColumns.split(' ')[0]);
        var gap = parseFloat(style.gap || style.rowGap || 12);
        var gr = grid.getBoundingClientRect();
        var gl = gr.left + window.scrollX;
        var gt = gr.top + window.scrollY;
        var gb = gr.bottom + window.scrollY;
        var rowH = colW;

        var colRanges = [], rowRanges = [];
        for (var c = 0; c < cols; c++) {
            var l = gl + c * (colW + gap);
            colRanges.push({ left: l, right: l + colW });
        }
        var total = grid.children.length;
        var rows = Math.ceil(total / cols);
        for (var r = 0; r < rows; r++) {
            var t = gt + r * (rowH + gap);
            var b = r === rows - 1 ? gb : t + rowH;
            rowRanges.push({ top: t, bottom: b });
        }

        var mx = e.pageX, my = e.pageY;

        var colIdx = 0, colMinD = Infinity;
        for (var i = 0; i < colRanges.length; i++) {
            if (mx >= colRanges[i].left && mx <= colRanges[i].right) { colIdx = i; break; }
            var d = Math.min(Math.abs(mx - colRanges[i].left), Math.abs(mx - colRanges[i].right));
            if (d < colMinD) { colMinD = d; colIdx = i; }
        }
        var rowIdx = 0, rowMinD = Infinity;
        for (var i = 0; i < rowRanges.length; i++) {
            if (my >= rowRanges[i].top && my <= rowRanges[i].bottom) { rowIdx = i; break; }
            var d = Math.min(Math.abs(my - rowRanges[i].top), Math.abs(my - rowRanges[i].bottom));
            if (d < rowMinD) { rowMinD = d; rowIdx = i; }
        }

        // 视觉排序做映射（临时清除 transform 避免干扰）
        var items = Array.from(grid.children).filter(function(el) { return !el.classList.contains('album-photo-placeholder'); });
        items.forEach(function(el) {
            el.dataset._st = el.style.transform;
            el.style.transition = 'none';
            el.style.transform = '';
        });
        void grid.offsetHeight;
        items.sort(function(a, b) {
            var ra = a.getBoundingClientRect(), rb = b.getBoundingClientRect();
            return (ra.top - rb.top) || (ra.left - rb.left);
        });
        items.forEach(function(el) {
            el.style.transform = el.dataset._st || '';
        });

        var idx = rowIdx * cols + colIdx;
        if (idx >= items.length) return null;
        return idx >= 0 ? items[idx] : items[0];
    }

    var dragItems = grid.querySelectorAll('.album-photo-item.draggable');
    dragItems.forEach(function(item) {
        item.addEventListener('mousedown', function(e) {
            if (e.button !== 0) return;
            if (e.target.closest('button') || e.target.closest('.album-photo-actions')) return;
            e.preventDefault();

            dragItem = this;
            startX = e.pageX; startY = e.pageY;
            isDragging = false;
            lastTarget = null; lastMove = 0;

            function onMove(e) {
                if (!isDragging && Math.abs(e.pageX - startX) + Math.abs(e.pageY - startY) > 5) {
                    isDragging = true;

                    ghost = document.createElement('img');
                    var si = dragItem.querySelector('img');
                    ghost.src = si ? si.src : '';
                    ghost.style.cssText = 'position:fixed;z-index:100010;pointer-events:none;opacity:0.85;width:120px;height:120px;object-fit:cover;border-radius:8px;box-shadow:0 8px 30px rgba(0,0,0,0.3);border:2px solid #00a1d6;';
                    ghost.style.left = (e.clientX - offsetX) + 'px';
                    ghost.style.top = (e.clientY - offsetY) + 'px';
                    document.body.appendChild(ghost);

                    placeholder = document.createElement('div');
                    placeholder.className = 'album-photo-placeholder';
                    grid.replaceChild(placeholder, dragItem);
                }

                if (isDragging && ghost && placeholder) {
                    autoScroll(e);
                    ghost.style.left = (e.clientX - offsetX) + 'px';
                    ghost.style.top = (e.clientY - offsetY) + 'px';

                    var t = getInsertPoint(e);
                    if (t !== lastTarget && Date.now() - lastMove > 150) {
                        var all = grid.querySelectorAll('.album-photo-item, .album-photo-placeholder');
                        all.forEach(function(el) { el.style.transition = 'none'; el.style.transform = ''; });
                        var prePos = new Map();
                        all.forEach(function(el) { var r = el.getBoundingClientRect(); prePos.set(el, { x: r.left, y: r.top }); });

                        if (t) grid.insertBefore(placeholder, t);
                        else if (placeholder !== grid.lastChild) grid.appendChild(placeholder);

                        var moved = grid.querySelectorAll('.album-photo-item, .album-photo-placeholder');
                        moved.forEach(function(el) {
                            var o = prePos.get(el);
                            if (!o) return;
                            var nr = el.getBoundingClientRect();
                            var dx = o.x - nr.left, dy = o.y - nr.top;
                            if (Math.abs(dx) > 0.5 || Math.abs(dy) > 0.5) {
                                el.style.transition = 'none';
                                el.style.transform = 'translate3d(' + dx + 'px, ' + dy + 'px, 0)';
                            }
                        });
                        grid.offsetHeight;
                        requestAnimationFrame(function() {
                            moved.forEach(function(el) {
                                el.style.transition = 'transform 0.35s ease';
                                el.style.transform = '';
                            });
                        });

                        lastTarget = t;
                        lastMove = Date.now();
                    }
                }
            }

            function onUp(e) {
                stopAutoScroll();
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);

                if (isDragging && dragItem && placeholder && placeholder.parentNode) {
                    placeholder.parentNode.replaceChild(dragItem, placeholder);
                }
                if (ghost && ghost.parentNode) ghost.parentNode.removeChild(ghost);
                if (isDragging) savePhotoOrder(albumId);
                dragItem = ghost = placeholder = null;
                isDragging = false;
                lastTarget = null;
            }

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        });
    });
}
