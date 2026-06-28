<?php
class VideoController
{
    const UPLOAD_DIR = '/static/videos/';
    const MAX_FILE_SIZE = 500 * 1024 * 1024; // 500MB
    const ALLOWED_EXT = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv', 'flv', 'wmv'];

    // 处理文件上传，返回相对URL或false
    private function handleUpload(): string|false
    {
        $file = $_FILES['video_file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        if ($file['size'] > self::MAX_FILE_SIZE) {
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => '文件大小超过限制（最大500MB）'];
            return false;
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXT)) {
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => '不支持的视频格式，仅支持：' . implode(', ', self::ALLOWED_EXT)];
            return false;
        }
        $dir = ROOT_DIR . '/public' . self::UPLOAD_DIR;
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $filename = uniqid('vid_', true) . '.' . $ext;
        $dest = $dir . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => '文件保存失败，请重试'];
            return false;
        }
        return self::UPLOAD_DIR . $filename;
    }

    // 删除旧的本地上传文件
    private function removeLocalFile(string $url): void
    {
        if (str_starts_with($url, self::UPLOAD_DIR)) {
            $path = ROOT_DIR . '/public' . $url;
            if (file_exists($path)) @unlink($path);
        }
    }

    // 视频列表
    public function index()
    {
        $videos = VideoModel::getAll('episode', 'ASC');
        require ROOT_DIR . '/views/video/index.php';
    }

    // 播放页面
    public function watch()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) become404page();
        $video = VideoModel::getById($id);
        if (!$video) become404page();
        require ROOT_DIR . '/views/video/watch.php';
    }

    // 管理员发布视频
    public function create()
    {
        if (!isset($_SESSION['username']) || !canManageActivity($_SESSION['permissions'])) {
            become403page();
        }
        require ROOT_DIR . '/views/video/create.php';
    }

    // 保存视频
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') become404page();
        if (!isset($_SESSION['username']) || !canManageActivity($_SESSION['permissions'])) {
            become403page();
        }
        verifyCsrfToken();

        $episode   = (int)($_POST['episode'] ?? 1);
        $title     = trim($_POST['title'] ?? '');
        $desc      = trim($_POST['description'] ?? '');
        $thumbnail = trim($_POST['thumbnail'] ?? '');

        if (empty($title)) {
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => '标题不能为空'];
            header('Location: /video/create');
            exit;
        }

        $videoUrl = '';

        // 优先处理文件上传
        if (!empty($_FILES['video_file']['name']) && $_FILES['video_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $videoUrl = $this->handleUpload();
            if ($videoUrl === false) {
                header('Location: /video/create');
                exit;
            }
        } else {
            $videoUrl = trim($_POST['video_url'] ?? '');
        }

        if (empty($videoUrl)) {
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => '请提供视频链接或上传视频文件'];
            header('Location: /video/create');
            exit;
        }

        $id = VideoModel::create($episode, $title, $videoUrl, $desc, $thumbnail ?: null);
        if ($id) {
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => '视频发布成功'];
            header('Location: /video/index');
        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => '发布失败'];
            header('Location: /video/create');
        }
        exit;
    }

    // 编辑视频（管理员）
    public function edit()
    {
        if (!isset($_SESSION['username']) || !canManageActivity($_SESSION['permissions'])) {
            become403page();
        }
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) become404page();
        $video = VideoModel::getById($id);
        if (!$video) become404page();
        require ROOT_DIR . '/views/video/edit.php';
    }

    // 更新视频
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') become404page();
        if (!isset($_SESSION['username']) || !canManageActivity($_SESSION['permissions'])) {
            become403page();
        }
        verifyCsrfToken();

        $id        = (int)($_POST['id'] ?? 0);
        $episode   = (int)($_POST['episode'] ?? 1);
        $title     = trim($_POST['title'] ?? '');
        $desc      = trim($_POST['description'] ?? '');
        $thumbnail = trim($_POST['thumbnail'] ?? '');

        if (!$id || empty($title)) {
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => '参数不完整'];
            header('Location: /video/index');
            exit;
        }

        $old = VideoModel::getById($id);

        // 如果有新文件上传，替换旧的
        if (!empty($_FILES['video_file']['name']) && $_FILES['video_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $videoUrl = $this->handleUpload();
            if ($videoUrl === false) {
                header('Location: /video/edit?id=' . $id);
                exit;
            }
            // 删除旧文件
            if ($old && !empty($old['video_url'])) {
                $this->removeLocalFile($old['video_url']);
            }
        } else {
            $videoUrl = trim($_POST['video_url'] ?? '');
        }

        if (empty($videoUrl)) {
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => '请提供视频链接或上传视频文件'];
            header('Location: /video/edit?id=' . $id);
            exit;
        }

        VideoModel::update($id, $episode, $title, $videoUrl, $desc, $thumbnail ?: null);
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => '视频已更新'];
        header('Location: /video/index');
        exit;
    }

    // 删除视频
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') become404page();
        if (!isset($_SESSION['username']) || !canManageActivity($_SESSION['permissions'])) {
            become403page();
        }
        verifyCsrfToken();

        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $video = VideoModel::getById($id);
            if ($video && !empty($video['video_url'])) {
                $this->removeLocalFile($video['video_url']);
            }
            VideoModel::delete($id);
        }
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => '视频已删除'];
        header('Location: /video/index');
        exit;
    }
}
