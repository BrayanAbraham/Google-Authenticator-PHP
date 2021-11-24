<?php

class Request {

    private $type;
    private $queryParams;
    private $postData;
    private $values;

    public function __construct(){
        $this->type = 'GET';
        $this->queryParams = [];
        $this->postData = [];
    }

    public function parse(){
        $this->type = $_SERVER['REQUEST_METHOD'];
        $queryParams = $_SERVER["QUERY_STRING"];
        $queryParams = explode('&', $queryParams);
        foreach ($queryParams as $index => $query) {
            $query = explode('=', $query);
            $key = array_shift($query);
            $query = implode('=',$query);
            $this->queryParams[$key] = $query;
        }
        if($this->type === "POST"){
            $this->postData = json_decode(file_get_contents('php://input'), true);
        }
    }

    public function getValue($key){
        if(array_key_exists($key, $this->queryParams)){
            return $this->queryParams[$key];
        } else if(array_key_exists($key, $this->postData)){
            return $this->postData[$key];
        }
    }

    public function getType(){
        return $this->type;
    }

    public function getQueryParams(){
        return $this->queryParams;
    }

    public function getPostData(){
        return $this->postData;
    }

}