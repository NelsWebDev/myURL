<?php
class URL extends mysqli{
	
	public function __construct(){
		require_once(__DIR__."/config.php");
		parent::__construct(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
	}

	public function getData($filter=""){
		if($filter == ""){
			$results = $this->query("SELECT * FROM urls");
		}
		else{
			$stmt = $this->prepare("SELECT * FROM urls WHERE url=? OR alias=?");
			$stmt->bind_param("ss", $filter, $filter);
			$stmt->execute();
			$results =  $stmt->get_result();
		}
		while($row = $results->fetch_assoc()){
			$url['url'][] = $row['url'];
			$url['alias'][] = $row['alias'];
			$url['clicks'][] = $row['clicks'];
		}
		if(!isset($url)){
			$url['url'] = array();
			$url['alias'] = array();
			$url['clicks'] = array();
		}
		return $url;
	}
	public static function inArray($needle, $haystack){
		return in_array( strtolower($needle), array_map("strtolower", $haystack) );
	}
	public function URLExists($url){
		if($this->inArray($url, $this->getData($url)['url']) )
			return true;
		else
			return false;
	}
	public function aliasExists($alias){
		if($this->inArray($alias, $this->getData($alias)['alias']) )
			return true;
		else
			return false;
	}
	public function getURL($alias){
		if($this->aliasExists($alias))
			return $this->getData($alias)['url'][0];
		else
			return false;
	}
	public function getAlias($url){
		if($this->aliasExists($url))
			return $this->getData($url)['alias'][0];
		else
			return false;
	}
	public static function alphaNumeric($verify){
		return ctype_alnum($verify);
	}
	public static function validURL($url){
		return filter_var($url, FILTER_VALIDATE_URL);
    }
	public static function generateAlias($url){
		return substr(md5(time().$url), 0, 6);
	}
	public function addURL($url, $alias=""){
        $this->addError = ""; //
		if($alias == "" || empty($alias)){
			$alias = $this->generateAlias($url);
		}
		$this->alias = $alias;
		if($this->aliasExists($alias) || !$this->alphaNumeric($alias) || $url == "" || !$this->validURL($url)){
			if($this->aliasExists($alias))
				$this->addError = BASEPATH."/$alias is taken";
			elseif($url == "")
				$this->addError = "Please specify a URL";
			elseif(!$this->alphaNumeric($alias))
				$this->addError = "Alias must be alphanumeric!";
			elseif(!$this->validURL($url))
				$this->addError = "Inavlid URL";
			else
				$this->addError = "An unknown error occurred!";
		}

		else{
			$stmt = $this->prepare("INSERT INTO urls (url, alias) VALUES (?, ?) ");
			$stmt->bind_param("ss", $url, $alias);
			$stmt->execute();
			return true;
		}

	}

	public function deleteAlias($alias){
		if($this->aliasExists($alias)){
			try{
				$stmt = $this->prepare("DELETE FROM urls WHERE alias=?");
				$stmt->bind_param("s", $alias);
				$stmt->execute();
				return true;
			}
			catch(Exception $e){
				return false;
			}
		}
		else
			return false;
	}

	public function deleteURL($url){
		if($this->URLExists($url)){
			try{
				$stmt = $this->prepare("DELETE FROM urls WHERE url=?");
				$stmt->bind_param("s", $url);
				$stmt->execute();
				return true;
			}
			catch(Exception $e){
				return false;
			}
		}
		else
			return false;
	}
}
