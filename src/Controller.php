<?php
/**
 *
 * Copyright  FaShop
 * License    http://www.fashop.cn
 * link       http://www.fashop.cn
 * Created by FaShop.
 * User: hanwenbo
 * Date: 2017/11/21
 * Time: 上午11:03
 *
 */

namespace fashop;

use Conf\Config as EsConfig;
use Core\AbstractInterface\AbstractController;
use Core\Swoole\Server;
use fashop\exception\ValidateException;

abstract class Controller extends AbstractController {

	protected $app;
	/**
	 * @var \fashop\View 视图类实例
	 */
	protected $view;
	/**
	 * @var \fashop\Request Request实例
	 */
	protected $request;
	// 验证失败是否抛出异常
	protected $failException = false;
	// 是否批量验证
	protected $batchValidate = false;

	function afterAction() {
		// 初始化，目的清理上一次请求的static记录
		\fashop\Request::instance()->clearInstance();
		// 清理上一次请求的model关联static记录
		\fashop\Loader::clearInstance();

	}

	public function index() {
		return $this->send(-1, [], "NOT FOUND");
	}

	function actionNotFound($actionName = null, $arguments = null) {
		return $this->send(-1, [], "actionNotFound");
	}

	function shutdown() {
		Server::getInstance()->getServer()->shutdown();
	}

	function router() {
		return $this->send(-1, [], "your router not end");
	}

	function send($code = 0, $data = [], $message = null) {
		$this->response()->withAddedHeader('Access-Control-Allow-Origin', EsConfig::getInstance()->getConf('access_control_allow_origin'));
		$this->response()->withAddedHeader('Content-Type', 'application/json; charset=utf-8');
		$this->response()->withAddedHeader('Access-Control-Allow-Headers', EsConfig::getInstance()->getConf('access_control_allow_headers'));
		$this->response()->withAddedHeader('Access-Control-Allow-Methods', EsConfig::getInstance()->getConf('access_control_allow_methods'));
		$this->response()->withStatus(200);
		$content = [
			"code"   => $code,
			"result" => $data,
			"msg"    => $message,
		];
		$this->response()->getBody()->write(json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
	}

	function getPageLimit() {
		$get  = input('get.');
		$page = isset($get['page']) ? $get['page'] : 1;
		$rows = isset($get['rows']) ? $get['rows'] : 10;
		return $page . ',' . $rows;
	}

	/**
	 * 加载模板输出
	 * @access protected
	 * @param string $template 模板文件名
	 * @param array  $vars     模板输出变量
	 * @param array  $replace  模板替换
	 * @param array  $config   模板参数
	 * @return mixed
	 */
	protected function fetch($template = '', $vars = [], $replace = [], $config = []) {
		//		return $this->view->fetch($template, $vars, $replace, $config);
	}

	/**
	 * 渲染内容输出
	 * @access protected
	 * @param string $content 模板内容
	 * @param array  $vars    模板输出变量
	 * @param array  $replace 替换内容
	 * @param array  $config  模板参数
	 * @return mixed
	 */
	protected function display($content = '', $vars = [], $replace = [], $config = []) {
		//		return $this->view->display($content, $vars, $replace, $config);
	}

	/**
	 * 模板变量赋值
	 * @access protected
	 * @param mixed $name  要显示的模板变量
	 * @param mixed $value 变量的值
	 * @return void
	 */
	protected function assign($name, $value = '') {
		//		$this->view->assign($name, $value);
	}

	/**
	 * 初始化模板引擎
	 * @access protected
	 * @param array|string $engine 引擎参数
	 * @return void
	 */
	protected function engine($engine) {
		//		$this->view->engine($engine);
	}

	/**
	 * 设置验证失败后是否抛出异常
	 * @access protected
	 * @param bool $fail 是否抛出异常
	 * @return $this
	 */
	protected function validateFailException($fail = true) {
		$this->failException = $fail;
		return $this;
	}

	/**
	 * 验证数据
	 * @access protected
	 * @param array        $data     数据
	 * @param string|array $validate 验证器名或者验证规则数组
	 * @param array        $message  提示信息
	 * @param bool         $batch    是否批量验证
	 * @param mixed        $callback 回调方法（闭包）
	 * @return array|string|true
	 * @throws ValidateException
	 */
	protected function validate($data, $validate, $message = [], $batch = false, $callback = null) {
		if (is_array($validate)) {
			$v = Loader::validate();
			$v->rule($validate);
		} else {
			if (strpos($validate, '.')) {
				// 支持场景
				list($validate, $scene) = explode('.', $validate);
			}
			$v = Loader::validate($validate);
			if (!empty($scene)) {
				$v->scene($scene);
			}
		}
		// 是否批量验证
		if ($batch || $this->batchValidate) {
			$v->batch(true);
		}

		if (is_array($message)) {
			$v->message($message);
		}

		if ($callback && is_callable($callback)) {
			call_user_func_array($callback, [$v, &$data]);
		}

		if (!$v->check($data)) {
			if ($this->failException) {
				throw new ValidateException($v->getError());
			} else {
				return $v->getError();
			}
		} else {
			return true;
		}
	}

}