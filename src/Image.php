<?php
namespace fashop;
use Core\Utility\File;
use Core\Utility\Random;
use Intervention\Image\ImageManagerStatic as ImageManage;

/**
 * todo 拓展oss上传
 * todo 根据bean的写法再优化下 有些代码不规范
 * 如果出现第三方存储需要规范接口文件 定义 抽象类
 */
class Image {
	protected static $instance;
	// 预处理
	private $preCall;
	/**
	 * 裁剪配置
	 * 值分别为 宽，高，圆角，背景色....等等未来再拓展
	 */
	private $cropOption = [
		'bmid'  => [440, null],
		'thumb' => [220, null],
	];
	// 域名
	private $domain = '';
	// 裁剪列表
	private $cropImages = [];
	// 原图
	private $originFileInfo = [];
	// 存放目录
	private $targetFolder = 'Upload';
	// 图片后缀
	private $ext;
	// 错误提示
	private $error;
	// 文件名（不包含后缀）
	private $fileName;
	// 原始图片名字（不包含后缀）
	private $originName;
	// 图片类型
	private $mediaType;
	// 名字随机长度
	private $randNameLength = 15;
	// 水印
	private $watermark = false;
	// 水印目录
	private $watermarkPath = 'Public/watermark.png';
	// 文件
	private $file;
	// 支持类型
	private $allowFileType = ['gif', 'jpg', 'jpeg', 'bmp', 'png'];
	/**
	 * 最终返回
	 *
	 * [
	 * 	 origin : {
	 * 	 	path : xxx,
	 * 	 },
	 * 	 thumb : {
	 * 	 	path:xxx,
	 * 	 },
	 * 	 bmid:{
	 * 	 	path:xxx
	 * 	 }
	 * ]
	 * @datetime 2017-11-01T14:54:24+0800
	 *
	 * 要实现的操作方式  Image::getInstance()->create($file)->crop(['bmid'=>440,'thumb'=>220])->getImages();
	 * 默认自动创建年月日文件夹todo 拓展
	 * @author 韩文博
	 */
	function __construct($preCall) {
		$this->preCall = $preCall;
		ImageManage::configure(array('driver' => 'imagick'));
		// todo 拓展
		$this->targetFolder = ROOT.DS.$this->targetFolder.DS.date( 'Ymd' );
	}

	static function getInstance(callable $preCall = null) {
		if (!isset(self::$instance)) {
			self::$instance = new static($preCall);
		}
		return self::$instance;
	}
	/**
	 * 设置目标文件夹
	 * @datetime 2017-11-01T15:13:54+0800
	 * @author 韩文博
	 * @param    string $path
	 */
	public function setTargetFloder(string $path) {
		$this->targetFolder = $path;
	}
	private function createTargetFloder() {
		File::createDir($this->targetFolder);
	}
	/**
	 * 设置允许的文件类型
	 * @datetime 2017-11-01T16:51:04+0800
	 * @author 韩文博
	 */
	public function setAllowFileType(array $types) {
		$this->setAllowFileType($types);
		return $this;
	}
	/**
	 * 创建
	 * @datetime 2017-11-01T14:42:22+0800
	 * @author 韩文博
	 * @param mixed $file stream Core\Http\Message\UploadFile | string base64_content
	 * return
	 */
	public function create($file) {
		$this->domain = Config::get('domain') ? Config::get('domain') : Request::instance()->domain();
		// 创建文件 todo 第三方依赖不创建
		$this->createTargetFloder();
		// 文件上传
		if ($file instanceof \Core\Http\Message\UploadFile) {
			$this->fileUpload($file);
		} elseif (is_string($file) && strstr($file, "data:image") && strstr($file, ";base64")) {
			// base64上传
			$this->base64Upload($file);
		} else {
			throw new \Exception("文件格式不对");
		}
		return $this;
	}
	/**
	 * 获得媒体类型
	 * @datetime 2017-11-01T18:19:19+0800
	 * @author 韩文博
	 * @return   string
	 */
	public function getMediaType() {
		return $this->mediaType;
	}
	/**
	 * Stream上传
	 * @datetime 2017-11-01T18:19:06+0800
	 * @author 韩文博
	 * @param    \Core\Http\Message\UploadFile $file
	 */
	private function fileUpload(\Core\Http\Message\UploadFile $file) {
		$this->file = $file;

		// todo 细想想是否有问题
		if ($file->getError()) {
			throw new \Exception($file->getError());
		}

		$this->mediaType  = $file->getClientMediaType();
		$this->ext        = strtolower(explode('/', $this->mediaType)[1]);
		$this->originName = $file->getClientFilename();

		if (!in_array($this->ext, $this->allowFileType)) {
			throw new \Exception("不支持该类型");
		}

		// 生成随机名字
		$this->fileName = Random::randStr($this->randNameLength);

		// 目标全名
		$target_file_name = "{$this->targetFolder}/{$this->fileName}.{$this->ext}";

		// 移动原图到存放目录
		$move_result = $file->moveTo($target_file_name);

		// 源文件信息
		$this->originFileInfo = $this->getImageInfoFormatData($target_file_name);

		if ($move_result == false) {
			throw new \Exception("移动文件失败");
		}
	}
	/**
	 * base64上传
	 * @datetime 2017-11-01T14:46:58+0800
	 * @author 韩文博
	 * @param    string $base64_content
	 *
	 */
	public function base64Upload(string $base64_content) {
		$this->file = $base64_content;

		// data:image/png;base64,
		list($head, $content) = explode(",", $this->file);
		$this->mediaType      = str_replace(';base64', '', str_replace('data:', '', $head));
		$this->ext            = strtolower(explode('/', $this->mediaType)[1]); // todo 单独来个方法设置
		$this->originName     = "base64";
		if (!in_array($this->ext, $this->allowFileType)) {
			throw new \Exception("不支持该类型");
		}

		// 生成随机名字
		$this->fileName = Random::randStr($this->randNameLength);

		// 目标全名
		$target_file_name = "{$this->targetFolder}/{$this->fileName}.{$this->ext}";

		// 移动原图到存放目录
		$move_result = file_put_contents($target_file_name, base64_decode($content));
		if ($move_result == false) {
			throw new \Exception("移动文件失败");
		}
		// 源文件信息
		$this->originFileInfo = $this->getImageInfoFormatData($target_file_name);
		return $this;
	}
	/**
	 * 裁剪
	 * todo 目前只根据宽度裁剪 有点太局限本项目了 再优化
	 * 返回裁剪后的内容
	 * [
	 * 	{path路径},{...}
	 * ]
	 * @param array $options 格式[
	'bmid'  => 440,
	'thumb' => 200,
	]
	 * @datetime 2017-11-01T14:44:46+0800
	 * @author 韩文博
	 */
	public function crop(array $options = []) {
		// todo 不允许键值为origin
		$this->cropOption = empty($options) ?: $options;
		// 裁剪
		$img = ImageManage::make($this->originFileInfo['path']);
		foreach ($this->cropOption as $suffix => $option) {
			list($width, $height) = $option;
			$img->resize($width, $height, function ($constraint) {
				$constraint->aspectRatio();
			});
			// 水印 todo 位置设置 目前是左上角
			if ($this->watermark === true) {
				$img->insert('Public/watermark.png');
			}
			$target_file_name = "{$this->targetFolder}/{$this->fileName}_{$suffix}.{$this->ext}";

			// 生成略显图
			$img->save($target_file_name);

			// 记录裁剪图片的信息
			$this->cropImages[$suffix] = $this->getImageInfoFormatData($target_file_name);
		}
		return $this;
	}
	/**
	 * 图片信息格式
	 * @datetime 2017-11-01T20:45:54+0800
	 * @author 韩文博
	 * @param    string $path
	 * @param    int $size
	 */
	private function getImageInfoFormatData(string $path) {
		if (!file_exists($path)) {
			throw new \Exception("该文件不存在");
		}
		// todo 有第三方存储再说
		$data = [
			// 相对路径
			'path' => '',
			// 文件字节
			'size' => 0,
			// 媒体类型
			'type' => '',
			// 链接地址
			'url'  => '',
		];
		$data['path'] = $path;
		$data['size'] = filesize($path);
		$data['type'] = $this->mediaType;
		$data['url']  = "{$this->domain}/{$path}";
		return $data;
	}
	/**
	 * 获得错误
	 * 暂时没用，还没想好
	 * @datetime 2017-11-01T18:17:20+0800
	 * @author 韩文博
	 * @return array
	 */
	public function getError() {
		return $this->error;
	}
	/**
	 * 获得上传后的图片列表
	 * @datetime 2017-11-01T14:56:22+0800
	 * @author 韩文博
	 * @return   array
	 */
	public function getImages() {
		return array_merge([
			'origin' => $this->getOriginImage(),
		], $this->getCropImages());
	}
	/**
	 * 获得裁剪图
	 * @datetime 2017-11-01T18:16:00+0800
	 * @author 韩文博
	 * @return array
	 */
	public function getCropImages() {
		return $this->cropImages;
	}
	/**
	 * 获得原始图
	 * @datetime 2017-11-01T18:16:08+0800
	 * @author 韩文博
	 * @return array
	 */
	public function getOriginImage() {
		return $this->originFileInfo;
	}
	/**
	 * 设置域名
	 * @datetime 2017-11-01T21:25:23+0800
	 * @author 韩文博
	 * todo 约束url格式
	 */
	public function setDomain(string $url) {
		$this->domain = $url;
		return $this;
	}
}
