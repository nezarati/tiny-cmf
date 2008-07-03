<?
/**
 *	Akismet anti-comment spam service
*/
namespace API;
class Akismet {
	const SERVER = 'rest.akismet.com', PORT = 80, VERSION = 1.1, API_KEY = '';
	protected $property = array();
	public function __construct($blogURL) {
		$this->blog = $blogURL;
		$this->user_agent = $_SERVER['HTTP_USER_AGENT'];
		$this->referrer = $_SERVER['HTTP_REFERER'];
		$this->user_ip = $_SERVER['REMOTE_ADDR'];
		/*
			@ <comment_author>
			@ <comment_author_email>
			@ <comment_author_url>
			@ <comment_content>
			@ {comment_type}
			@ {permalink}
		*/
	}
	public function __set($name, $value) {
		$this->property[$name] = $value;
	}
	public function __get($name) {
		return $this->property[$name];
	}
	
	/**
	 *	Submit ham/spam that is incorrectly tagged as spam/ham.
	 *
	 *	Using this function will make you a good citizen as it helps Akismet to learn from its mistakes. This will improve the service for everybody.
	 */
	public function __call($method, $arg) {
		$path = array('check' => 'comment-check', 'submitSpam' => 'submit-spam', 'submitHam' => 'submit-ham');
		return \HTTP::request('http://'.self::API_KEY.'.'.self::SERVER.'/'.self::VERSION.'/'.$path[$method], $this->property);
	}
	public function isKeyValid() { // Check to see if the key is valid
		return \HTTP::request('http://'.self::SERVER.'/'.self::VERSION.'/verify-key', array('key' => self::API_KEY, 'blog' => $this->blog)) == 'valid';
	}
	public function isSpam() {
		$response = $this->check();
		if ($response == 'invalid' && !$this->isKeyValid())
			throw new \Exception('The Wordpress API key passed to the Akismet constructor is invalid. Please obtain a valid one from http://wordpress.com/api-keys/');
		return $response == 'true';
	}
}
