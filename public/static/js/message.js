/* 全局 Toast 和 Modal 工具函数 */

// Toast 提示
function showToast(msg, type) {
    type = type || 'info';
    var t = document.getElementById('globalToast');
    if (!t) {
        t = document.createElement('div');
        t.id = 'globalToast';
        t.className = 'global-toast';
        document.body.appendChild(t);
    }
    t.textContent = msg;
    t.className = 'global-toast ' + type + ' show';
    clearTimeout(t._timer);
    t._timer = setTimeout(function() { t.classList.remove('show'); }, 2500);
}

// Modal 确认框
function showConfirmModal(options) {
    var msg = options.message || '确定执行此操作吗？';
    var confirmText = options.confirmText || '确定';
    var confirmClass = options.confirmClass || ''; // 'btn-confirm-primary' for blue
    var onConfirm = options.onConfirm || function(){};
    var onCancel = options.onCancel || function(){};

    // 创建或复用模态框
    var overlay = document.getElementById('globalConfirmModal');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'globalConfirmModal';
        overlay.className = 'global-modal-overlay';
        overlay.innerHTML =
            '<div class="global-modal-box">' +
                '<p id="globalConfirmMsg"></p>' +
                '<div class="modal-btn-group">' +
                    '<button class="modal-btn-cancel" id="globalConfirmCancel">取消</button>' +
                    '<button class="modal-btn-confirm" id="globalConfirmOk"></button>' +
                '</div>' +
            '</div>';
        document.body.appendChild(overlay);
    }

    var msgEl = document.getElementById('globalConfirmMsg');
    var okBtn = document.getElementById('globalConfirmOk');
    var cancelBtn = document.getElementById('globalConfirmCancel');

    msgEl.textContent = msg;
    okBtn.textContent = confirmText;
    okBtn.className = 'modal-btn-confirm' + (confirmClass ? ' ' + confirmClass : '');

    // 移除旧的事件监听器（通过克隆替换节点）
    var newOkBtn = okBtn.cloneNode(true);
    okBtn.parentNode.replaceChild(newOkBtn, okBtn);
    var newCancelBtn = cancelBtn.cloneNode(true);
    cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);

    newOkBtn.addEventListener('click', function() {
        overlay.classList.remove('active');
        onConfirm();
    });

    newCancelBtn.addEventListener('click', function() {
        overlay.classList.remove('active');
        onCancel();
    });

    // 点击遮罩关闭
    overlay.onclick = function(e) {
        if (e.target === overlay) {
            overlay.classList.remove('active');
            onCancel();
        }
    };

    overlay.classList.add('active');
}

// 兼容旧版 Message 接口（showMessage 函数使用）
function Message(options) {
    var type = options.type || 'info';
    var text = options.text || '提示信息';
    var duration = options.duration || 3000;
    showToast(text, type);
    if (duration > 0) {
        // Toast 自动消失已由 showToast 内部处理
    }
}
Message.success = function(text, duration) { showToast(text, 'success'); };
Message.error = function(text, duration) { showToast(text, 'error'); };
Message.warning = function(text, duration) { showToast(text, 'warning'); };
Message.info = function(text, duration) { showToast(text, 'info'); };
