<?php

namespace ezswoole\utils\image;

use EasySwoole\Core\Utility\File;
use EasySwoole\Core\Utility\Random;
use Intervention\Image\ImageManagerStatic as ImageManage;

/**
 * 如果出现第三方存储需要规范接口文件 定义 抽象类
 */
class Image
{
	protected static $instance;
	/**
	 * 裁剪配置
	 * 值分别为 宽，高，圆角，背景色....等等未来再拓展
	 */
	private $cropOption
		= [
			'bmid'  => [440, null],
			'thumb' => [220, null],
		];
	// 裁剪列表
	private $cropImages = [];
	// 原图
	private $originFileInfo = [];
	// 存放目录
	private $targetFolder;
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

	private $config
		= [
			'target_folder' => '',
		];

	/**
	 * 最终返回
	 *
	 * [
	 *     origin : {
	 *        path : xxx,
	 *     },
	 *     thumb : {
	 *        path:xxx,
	 *     },
	 *     bmid:{
	 *        path:xxx
	 *     }
	 * ]
	 * @datetime 2017-11-01T14:54:24+0800
	 *
	 * 要实现的操作方式  Image::getInstance()->create($file)->crop(['bmid'=>440,'thumb'=>220])->getImages();
	 * 默认自动创建年月日文件夹todo 拓展
	 * @author   韩文博
	 */
	function __construct( array $config = null )
	{
		ImageManage::configure( ['driver' => 'imagick'] );
		if( $config ){
			$this->config = array_merge( $this->config, $config );
		}

		if( !isset( $this->config['target_folder'] ) || empty( $this->config['target_folder'] ) ){
			$this->targetFolder = EASYSWOOLE_ROOT.DS.'Upload'.DS.date( 'Ymd' );
		} else{
			$this->targetFolde = $this->config['target_folder'];
		}
	}

	static function getInstance( array $config = null ) : Image
	{
		if( !isset( self::$instance ) || !empty( $config ) ){
			self::$instance = new static( $config );
		}
		return self::$instance;
	}

	/**
	 * 设置目标文件夹
	 * @datetime 2017-11-01T15:13:54+0800
	 * @author   韩文博
	 * @param    string $path
	 */
	public function setTargetFloder( string $path ) : void
	{
		$this->targetFolder = $path;
	}

	private function createTargetFloder() : void
	{
		File::createDir( $this->targetFolder );
	}

	/**
	 * 设置允许的文件类型
	 * @datetime 2017-11-01T16:51:04+0800
	 * @author   韩文博
	 */
	public function setAllowFileType( array $types ) : Image
	{
		$this->setAllowFileType( $types );
		return $this;
	}

	/**
	 * 创建
	 * @datetime 2017-11-01T14:42:22+0800
	 * @author   韩文博
	 * @param mixed $file stream Core\Http\Message\UploadFile | string base64_content
	 * @throws \Exception
	 */
	public function create( $file ) : Image
	{
		// 创建文件夹
		if( !file_exists( $this->targetFolder ) ){
			$this->createTargetFloder();
		}
		// 文件上传
		if( isset( $file['tmp_name'] ) ){
			$this->fileUpload( new \EasySwoole\Core\Http\Message\UploadFile( $file['tmp_name'], $file['size'], $file['error'], $file['name'], $file['type'] ) );
		} elseif( is_string( $file ) && strstr( $file, "data:image" ) && strstr( $file, ";base64" ) ){
			// base64上传
			$this->base64Upload( $file );
		} else{
			throw new \Exception( "文件格式不对" );
		}
		return $this;
	}

	/**
	 * 获得媒体类型
	 * @datetime 2017-11-01T18:19:19+0800
	 * @author   韩文博
	 * @return   string
	 */
	public function getMediaType() : string
	{
		return $this->mediaType;
	}

	/**
	 * Stream上传
	 * @datetime 2017-11-01T18:19:06+0800
	 * @author   韩文博
	 * @param    \EasySwoole\Core\Http\Message\UploadFile $file
	 * @throws \Exception
	 */
	private function fileUpload( \EasySwoole\Core\Http\Message\UploadFile $file ) : void
	{
		$this->file = $file;

		if( $file->getError() ){
			throw new \Exception( $file->getError() );
		}

		$this->mediaType  = $file->getClientMediaType();
		$this->ext        = strtolower( explode( '/', $this->mediaType )[1] );
		$this->originName = $file->getClientFilename();

		if( !in_array( $this->ext, $this->allowFileType ) ){
			throw new \Exception( "不支持该类型" );
		}

		// 生成随机名字
		$this->fileName = Random::randStr( $this->randNameLength );

		// 目标全名
		$target_file_name = "{$this->targetFolder}/{$this->fileName}.{$this->ext}";

		// 移动原图到存放目录
		$move_result = $file->moveTo( $target_file_name );

		// 源文件信息
		$this->originFileInfo = $this->getImageInfoFormatData( $target_file_name );

		if( $move_result == false ){
			throw new \Exception( "移动文件失败" );
		}
	}

	/**
	 * base64上传
	 * @datetime 2017-11-01T14:46:58+0800
	 * @author   韩文博
	 * @param    string $base64_content
	 * @throws \Exception
	 *
	 */
	public function base64Upload( string $base64_content ) : Image
	{
		$this->file = $base64_content;

		// data:image/png;base64,
		list( $head, $content ) = explode( ",", $this->file );
		$this->mediaType  = str_replace( ';base64', '', str_replace( 'data:', '', $head ) );
		$this->ext        = strtolower( explode( '/', $this->mediaType )[1] ); // todo 单独来个方法设置
		$this->originName = "base64";
		if( !in_array( $this->ext, $this->allowFileType ) ){
			throw new \Exception( "不支持该类型" );
		}

		// 生成随机名字
		$this->fileName = Random::randStr( $this->randNameLength );

		// 目标全名
		$target_file_name = "{$this->targetFolder}/{$this->fileName}.{$this->ext}";

		// 移动原图到存放目录
		$move_result = file_put_contents( $target_file_name, base64_decode( $content ) );
		if( $move_result == false ){
			throw new \Exception( "移动文件失败" );
		}
		// 源文件信息
		$this->originFileInfo = $this->getImageInfoFormatData( $target_file_name );
		return $this;
	}

	/**
	 * 裁剪
	 * todo 目前只根据宽度裁剪 有点太局限本项目了 再优化
	 * 返回裁剪后的内容
	 * [
	 *    {path路径},{...}
	 * ]
	 * @param array $options 格式[
	 *                       'bmid'  => 440,
	 *                       'thumb' => 200,
	 *                       ]
	 * @datetime 2017-11-01T14:44:46+0800
	 * @author   韩文博
	 * @throws \Exception
	 */

	public function crop( array $options = [] ) : Image
	{
		$this->cropOption = empty( $options ) ? $this->cropOption : $options;
		// 裁剪
		$img = ImageManage::make( $this->originFileInfo['path'] );
		foreach( $this->cropOption as $suffix => $option ){
			list( $width, $height ) = $option;
			$img->resize( $width, $height, function( $constraint ){
				$constraint->aspectRatio();
			} );
			// 水印 todo 位置设置 目前是左上角
			if( $this->watermark === true ){
				$img->insert( 'Public/watermark.png' );
			}
			$target_file_name = "{$this->targetFolder}/{$this->fileName}_{$suffix}.{$this->ext}";

			// 生成略显图
			$img->save( $target_file_name );

			// 记录裁剪图片的信息
			$this->cropImages[$suffix] = $this->getImageInfoFormatData( $target_file_name );
		}
		return $this;
	}

	/**
	 * 图片信息格式
	 * @datetime 2017-11-01T20:45:54+0800
	 * @author   韩文博
	 * @param    string $path
	 * @param    int    $size
	 * @throws \Exception
	 */
	private function getImageInfoFormatData( string $path ) : array
	{
		if( !file_exists( $path ) ){
			throw new \Exception( "该文件不存在" );
		}
		$data         = [
			// 纯名字
			'name' => '',
			// 相对路径
			'path' => '',
			// 文件字节
			'size' => 0,
			// 媒体类型
			'type' => '',
		];
		$data['name'] = $this->fileName;
		$data['path'] = $path;
		$data['size'] = filesize( $path );
		$data['type'] = $this->mediaType;
		return $data;
	}

	/**
	 * 获得错误
	 * 暂时没用，还没想好
	 * @datetime 2017-11-01T18:17:20+0800
	 * @author   韩文博
	 * @return array
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * 获得上传后的图片列表
	 * @datetime 2017-11-01T14:56:22+0800
	 * @author   韩文博
	 * @return   array
	 */
	public function getImages() : array
	{
		$list = array_merge( [
			'origin' => $this->getOriginImage(),
		], $this->getCropImages() );
		foreach( $list as $key => $image ){
			$image['path'] = str_replace( ROOT_PATH, '', $image['path'] );
			$list[$key]    = $image;
		}
		return $list;
	}

	/**
	 * 获得裁剪图
	 * @datetime 2017-11-01T18:16:00+0800
	 * @author   韩文博
	 * @return array
	 */
	public function getCropImages() : array
	{
		return $this->cropImages;
	}

	/**
	 * 获得原始图
	 * @datetime 2017-11-01T18:16:08+0800
	 * @author   韩文博
	 * @return array
	 */
	public function getOriginImage() : array
	{
		return $this->originFileInfo;
	}


}
