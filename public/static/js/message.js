/* 全局 Toast 和 Modal 工具函数 */

// Modal 输入框
function showInputModal(options) {
    var title = options.title || '请输入';
    var placeholder = options.placeholder || '';
    var defaultValue = options.defaultValue || '';
    var confirmText = options.confirmText || '确定';
    var onConfirm = options.onConfirm || function(text){};
    var onCancel = options.onCancel || function(){};

    var overlay = document.getElementById('globalInputModal');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'globalInputModal';
        overlay.className = 'global-modal-overlay';
        overlay.innerHTML =
            '<div class="global-modal-box input-modal-box">' +
                '<p id="globalInputTitle"></p>' +
                '<input id="globalInputField" class="global-modal-input-field" type="text">' +
                '<div class="modal-btn-group">' +
                    '<button class="modal-btn-cancel" id="globalInputCancel">取消</button>' +
                    '<button class="modal-btn-confirm btn-confirm-primary" id="globalInputOk"></button>' +
                '</div>' +
            '</div>';
        document.body.appendChild(overlay);
    }

    var titleEl = document.getElementById('globalInputTitle');
    var inputEl = document.getElementById('globalInputField');
    var okBtn = document.getElementById('globalInputOk');
    var cancelBtn = document.getElementById('globalInputCancel');

    titleEl.textContent = title;
    inputEl.value = defaultValue;
    inputEl.placeholder = placeholder;
    okBtn.textContent = confirmText;

    // 移除旧的事件监听器
    var newOkBtn = okBtn.cloneNode(true);
    okBtn.parentNode.replaceChild(newOkBtn, okBtn);
    var newCancelBtn = cancelBtn.cloneNode(true);
    cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);

    newOkBtn.addEventListener('click', function() {
        var text = inputEl.value.trim();
        if (!text) {
            showToast('请输入内容', 'warning');
            return;
        }
        overlay.classList.remove('active');
        onConfirm(text);
    });

    newCancelBtn.addEventListener('click', function() {
        overlay.classList.remove('active');
        onCancel();
    });

    // 回车提交
    inputEl.onkeydown = function(e) {
        if (e.key === 'Enter') newOkBtn.click();
    };

    overlay.classList.add('active');
    setTimeout(function() { inputEl.focus(); inputEl.select(); }, 100);
}

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

// Modal 评论输入框
function showCommentModal(options) {
    var placeholder = options.placeholder || '请输入评论内容...';
    var confirmText = options.confirmText || '发表评论';
    var onConfirm = options.onConfirm || function(text){};
    var onCancel = options.onCancel || function(){};

    var overlay = document.getElementById('globalCommentModal');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'globalCommentModal';
        overlay.className = 'global-modal-overlay';
        overlay.innerHTML =
            '<div class="global-modal-box comment-modal-box">' +
                '<p style="font-size:15px;font-weight:600;margin-bottom:12px;color:#333;">发表评论</p>' +
                '<textarea id="globalCommentInput" class="global-modal-comment-input" rows="3"></textarea>' +
                '<div class="modal-btn-group" style="margin-top:12px;">' +
                    '<button class="modal-btn-cancel" id="globalCommentCancel">取消</button>' +
                    '<button class="modal-btn-confirm btn-confirm-primary" id="globalCommentOk"></button>' +
                '</div>' +
            '</div>';
        document.body.appendChild(overlay);
    }

    var textarea = document.getElementById('globalCommentInput');
    var okBtn = document.getElementById('globalCommentOk');
    var cancelBtn = document.getElementById('globalCommentCancel');

    textarea.value = '';
    textarea.placeholder = placeholder;
    okBtn.textContent = confirmText;

    // 移除旧的事件监听器
    var newOkBtn = okBtn.cloneNode(true);
    okBtn.parentNode.replaceChild(newOkBtn, okBtn);
    var newCancelBtn = cancelBtn.cloneNode(true);
    cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);

    newOkBtn.addEventListener('click', function() {
        var text = textarea.value.trim();
        if (!text) {
            showToast('请输入评论内容', 'warning');
            return;
        }
        overlay.classList.remove('active');
        onConfirm(text);
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
    // 自动聚焦
    setTimeout(function() { textarea.focus(); }, 100);
}

// 兼容旧版 Message 接口（showMessage 函数使用）
function Message(options) {
    var type = options.type || 'info';
    var text = options.text || '提示信息';
    showToast(text, type);
}
Message.success = function(text, duration) { showToast(text, 'success'); };
Message.error = function(text, duration) { showToast(text, 'error'); };
Message.warning = function(text, duration) { showToast(text, 'warning'); };
Message.info = function(text, duration) { showToast(text, 'info'); };
