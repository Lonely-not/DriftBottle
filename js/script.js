// 漂流瓶系统前端交互脚本
document.addEventListener('DOMContentLoaded', function() {
    // 初始化工具提示
    const tooltips = document.querySelectorAll('[data-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip);
    });
    
    // 表单验证
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
    
    // 实时字符计数
    const textareas = document.querySelectorAll('textarea[data-max-length]');
    textareas.forEach(textarea => {
        const maxLength = textarea.getAttribute('data-max-length');
        const counter = document.createElement('div');
        counter.className = 'text-right text-muted small mt-1';
        counter.innerHTML = `剩余字数: <span>${maxLength}</span>`;
        textarea.parentNode.appendChild(counter);
        
        textarea.addEventListener('input', function() {
            const remaining = maxLength - this.value.length;
            counter.querySelector('span').textContent = remaining;
            
            if (remaining < 0) {
                counter.classList.add('text-danger');
            } else {
                counter.classList.remove('text-danger');
            }
        });
    });
    
    // 漂流瓶动画
    const bottles = document.querySelectorAll('.bottle-item');
    bottles.forEach(bottle => {
        bottle.addEventListener('mouseenter', function() {
            this.classList.add('bottle-animation');
        });
        
        bottle.addEventListener('mouseleave', function() {
            this.classList.remove('bottle-animation');
        });
    });
    
    // AJAX表单提交
    const ajaxForms = document.querySelectorAll('form[data-ajax]');
    ajaxForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // 显示加载状态
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 处理中...';
            
            fetch(this.action, {
                method: this.method,
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1500);
                    }
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                showAlert('请求失败: ' + error.message, 'danger');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    });
    
    // 显示警告框
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        `;
        
        const container = document.querySelector('.container') || document.body;
        container.prepend(alertDiv);
        
        // 自动关闭警告框
        setTimeout(() => {
            const alert = bootstrap.Alert.getOrCreateInstance(alertDiv);
            alert.close();
        }, 5000);
    }
    
    // 模态框处理
    const modalTriggers = document.querySelectorAll('[data-toggle="modal"]');
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function() {
            const modalId = this.getAttribute('data-target');
            const modal = new bootstrap.Modal(document.querySelector(modalId));
            modal.show();
        });
    });
    
    // 页面加载动画
    const content = document.querySelector('.main-content');
    if (content) {
        content.classList.add('fade-in');
    }
});

// 工具函数
const DriftBottle = {
    // 格式化时间
    formatTime: function(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        
        if (diff < 60000) { // 小于1分钟
            return '刚刚';
        } else if (diff < 3600000) { // 小于1小时
            return Math.floor(diff / 60000) + '分钟前';
        } else if (diff < 86400000) { // 小于1天
            return Math.floor(diff / 3600000) + '小时前';
        } else if (diff < 604800000) { // 小于1周
            return Math.floor(diff / 86400000) + '天前';
        } else {
            return date.toLocaleDateString();
        }
    },
    
    // 复制到剪贴板
    copyToClipboard: function(text) {
        navigator.clipboard.writeText(text).then(() => {
            console.log('文本已复制到剪贴板');
        }).catch(err => {
            console.error('无法复制文本: ', err);
        });
    },
    
    // 防抖函数
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    // 获取随机颜色
    getRandomColor: function() {
        const colors = ['#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6', '#1abc9c'];
        return colors[Math.floor(Math.random() * colors.length)];
    }
};

// 初始化页面特定功能
function initPage() {
    // 首页特定功能
    if (document.body.classList.contains('home-page')) {
        initHomePage();
    }
    
    // 发送页面特定功能
    if (document.body.classList.contains('send-page')) {
        initSendPage();
    }
    
    // 拾取页面特定功能
    if (document.body.classList.contains('pick-page')) {
        initPickPage();
    }
}

// 首页初始化
function initHomePage() {
    // 更新实时数据
    setInterval(updateStats, 30000);
    
    // 初始化图表
    if (typeof Chart !== 'undefined') {
        initCharts();
    }
}

// 发送页面初始化
function initSendPage() {
    const messageInput = document.getElementById('message');
    const emotionButtons = document.querySelectorAll('.emotion-btn');
    
    emotionButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const emotion = this.getAttribute('data-emotion');
            messageInput.value += emotion;
            messageInput.focus();
        });
    });
}

// 拾取页面初始化
function initPickPage() {
    const pickBtn = document.getElementById('pick-bottle-btn');
    if (pickBtn) {
        pickBtn.addEventListener('click', function() {
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 打捞中...';
            
            // 模拟打捞动画
            setTimeout(() => {
                fetch('api/pick_bottle.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            displayBottle(data.bottle);
                        } else {
                            showAlert(data.message, 'info');
                        }
                    })
                    .catch(error => {
                        showAlert('打捞失败: ' + error.message, 'danger');
                    })
                    .finally(() => {
                        this.innerHTML = '🎣 捞一个漂流瓶';
                    });
            }, 1500);
        });
    }
}

// 显示漂流瓶
function displayBottle(bottle) {
    const bottleDiv = document.createElement('div');
    bottleDiv.className = 'bottle-card card slide-in';
    bottleDiv.innerHTML = `
        <div class="card-header">
            <h5>漂流瓶 #${bottle.id}</h5>
            <small class="text-muted">${DriftBottle.formatTime(bottle.send_time)}</small>
        </div>
        <div class="card-body">
            <p class="bottle-message">${bottle.message}</p>
            <div class="bottle-meta">
                <span class="badge badge-primary">${bottle.sender_gender}</span>
                <span class="badge badge-secondary">${bottle.distance}公里外</span>
            </div>
        </div>
        <div class="card-footer">
            <button class="btn btn-sm btn-outline-primary reply-btn" data-id="${bottle.id}">💌 回复</button>
            <button class="btn btn-sm btn-outline-secondary keep-btn" data-id="${bottle.id}">📦 保存</button>
            <button class="btn btn-sm btn-outline-danger throw-back-btn" data-id="${bottle.id}">🌊 扔回海里</button>
        </div>
    `;
    
    document.getElementById('bottle-container').appendChild(bottleDiv);
}

// 初始化图表
function initCharts() {
    const ctx = document.getElementById('statsChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['周一', '周二', '周三', '周四', '周五', '周六', '周日'],
                datasets: [{
                    label: '发送量',
                    data: [12, 19, 15, 17, 14, 22, 18],
                    borderColor: '#3498db',
                    tension: 0.1,
                    fill: false
                }, {
                    label: '接收量',
                    data: [8, 12, 10, 14, 11, 16, 13],
                    borderColor: '#2ecc71',
                    tension: 0.1,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    }
}

// 更新统计数据
function updateStats() {
    fetch('api/stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelectorAll('[data-stat]').forEach(el => {
                    const stat = el.getAttribute('data-stat');
                    if (data[stat] !== undefined) {
                        el.textContent = data[stat];
                    }
                });
            }
        });
}

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', initPage);
