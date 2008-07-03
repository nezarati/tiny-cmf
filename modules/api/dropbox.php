<?
namespace API;
class Dropbox extends \MyOAuth {
	const URI_REQUEST_TOKEN = 'http://api.dropbox.com/0/oauth/request_token';
	const URI_AUTHORIZE = 'http://api.dropbox.com/0/oauth/authorize';
	const URI_ACCESS_TOKEN = 'http://api.dropbox.com/0/oauth/access_token';
	
	const SIGNATURE_METHOD = parent::SIG_METHOD_HMACSHA1, CONSUMER_KEY = '', CONSUMER_SECRET = '', OAUTH_TOKEN = '', OAUTH_TOKEN_SECRET = '', USER_ID = '';
	
	const FILE_CHUNK_SIZE = 262144000;
	
	public function getToken($email, $password) {
		return $this->fetch('http://api.dropbox.com/0/token', array(
				'email' => $email,
				'password' => $password,
			)
		);
	}
	public function account($email, $password, $firstName, $lastName) {
		return (bool) ($this->fetch('0/account', array('email' => $email, 'first_name' => $firstName, 'last_name' => $lastName, 'password' => $password), parent::HTTP_METHOD_GET, array(), FALSE) == 'OK');
	}
	public function accountInfo() {
		return $this->fetch('http://api.dropbox.com/0/account/info');
	}
	public function putFile($path, $file, $basename = NULL) {
		return $this->fetch('http://api-content.dropbox.com/0/files/dropbox/'.trim($path, '/'), array(), parent::HTTP_METHOD_POST, array('file' => array('basename' => $basename ?: pathinfo($file, PATHINFO_BASENAME), 'bytes' => is_array($file) ? $file['contents'] : (is_resource($file) ? stream_get_contents($file) : file_get_contents($file)))))->result == 'winner!';
	}
	public function putFileSplit($path, $file, $basename = NULL) {
		$basename || $basename = pathinfo($file, PATHINFO_BASENAME);
		for ($handle = fopen($file, 'r'), $piece = 1; !feof($handle); $piece++)
			if (!$this->putFile($path, array('contents' => fread($handle, self::FILE_CHUNK_SIZE)), $basename.'.'.str_pad($piece, 3, 0, STR_PAD_LEFT)))
				return FALSE;
		fclose($handle);
		return TRUE;
	}
	public function getFile($path) {
		return $this->fetch('http://api-content.dropbox.com/0/files/dropbox/'.$path, NULL, parent::HTTP_METHOD_GET, array(), FALSE);
	}
	public function metaData($path, $fileLimit = 10000, $hash = NULL, $list = TRUE) {
	
		$parameters['file_limit'] = (int) $fileLimit;
		if ($hash)
			$parameters['hash'] = $hash;
		$parameters['list'] = $list ? 'true': 'false';
		
		return $this->fetch('http://api.dropbox.com/0/metadata/dropbox/'.trim($path, '/'), $parameters);
	}
	
	public function thumbnails($path, $size = 'small') {
		$parameters['size'] = $size;
		return $this->fetch('http://api-content.dropbox.com/0/thumbnails/dropbox/'.$path, $parameters, parent::HTTP_METHOD_GET, array(), FALSE);
	}
	public function copy($fromPath, $toPath) {

		$parameters['from_path'] = $fromPath;
		$parameters['to_path'] = $toPath;
		$parameters['root'] = 'dropbox';

		return $this->fetch('http://api.dropbox.com/0/fileops/copy', $parameters);
	}
	public function createFolder($path) {

		$parameters['path'] = $path;
		$parameters['root'] = 'dropbox';

		return $this->fetch('http://api.dropbox.com/0/fileops/create_folder', $parameters);
	}
	public function delete($path) {

		$parameters['path'] = $path;
		$parameters['root'] = 'dropbox';

		return $this->fetch('http://api.dropbox.com/0/fileops/delete', $parameters);
	}
	public function move($fromPath, $toPath) {

		$parameters['from_path'] = $fromPath;
		$parameters['to_path'] = $toPath;
		$parameters['root'] = 'dropbox';

		return $this->fetch('http://api.dropbox.com/0/fileops/move', $parameters);
	}

	public static function getURL($service, $id) {
		return 'http://dl.dropbox.com/u/'.self::USER_ID.'/'.$service.'/'.$id;
	}
}
