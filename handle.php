<?php

header('Content-Type: application/json');


/*$result = [
	'status' => 'success',
	'message' => 'Đăng nhạc mới thành công: Re Loi - Cao Thai Son'
];
echo json_encode($result);
die();*/


if (isset($_POST['action']) && $_POST['action'] == 'create'){
	if (isset($_POST['url']) && $_POST['url'] != ''){
		new GetMP3('create', $_POST['url']);
	}else{
		echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy dữ liệu']);
	}
}elseif (isset($_POST['action']) && $_POST['action'] == 'delete'){
	
	if (isset($_POST['mp3_name']) && $_POST['mp3_name'] != ''){
		$mp3 = new GetMP3('delete', $_POST['mp3_name']);
	}else{
		echo json_encode(['status' => 'error']);
	}
	
}


class GetMP3{
	public function __construct($action, $url){
		if ($action == 'create'){
			$url = $this->remove_http($url);
			if (intval(strpos($url, 'mp3.zing.vn'))){
				$this->get_mp3_zing($url);
			}elseif (strpos($url, 'nhaccuatui.com')){
				$this->get_mp3_nhaccuatui($url);
			}
		}elseif ($action == 'delete'){
			$this->delete($url); //$url = $_POST['mp3_name']
		}
	}
	public function get_mp3_zing($url){
		$html = file_get_contents($url);
		$html = str_replace(array("\r\n", "\r", "\n", "\n\r"), "", $html);
		$html = preg_replace('!\s+!smi', ' ', $html);
		preg_match("/<div id=\"mp3Player\" loop=\"true\" xml=\"(.*?)\" class=\"none fnAudioPlayer\">/", $html, $data);
		$mp3_html =  file_get_contents($data[1]);
		$mp3_data = json_decode($mp3_html);
		$mp3_url =  $mp3_data->data[0]->source;
		$mp3_name = $mp3_data->data[0]->title.' - '.$mp3_data->data[0]->performer;
		$this->save_mp3($mp3_name, $mp3_url);
	}
	public function get_mp3_nhaccuatui($url){
		$html = file_get_contents($url);
		$html = str_replace(array("\r\n", "\r", "\n", "\n\r"), "", $html);
		$html = preg_replace('!\s+!smi', ' ', $html);
		preg_match("/Bạn vui lòng click để nghe bài hát. <p> <a href=\"(.*?)\" title=\"/", $html, $data);
		$mp3_url = $data[1];
		preg_match("/<div class=\"lyric\"> B&agrave;i h&aacute;t: (.*?)<br \/>/", $html, $data);
		$mp3_name = $data[1];
		$this->save_mp3($mp3_name, $mp3_url);
	}
	public function save_mp3($mp3_name, $mp3_url){
		if ($mp3_name == '' || $mp3_url == ''){
			$result['status'] = 'error';
			$result['message'] = 'Không tìm thấy dữ liệu từ URL';
			echo json_encode($result);
			exit();
		}
		$result = [];
		$mp3_name = html_entity_decode($mp3_name);
		$mp3_name = $this->vn_str_filter($mp3_name);
		$mp3_file = __DIR__.'/files/'.$mp3_name.'.mp3';
		if (!file_exists($mp3_file)){
			$mp3 = file_get_contents($mp3_url);
			file_put_contents($mp3_file, $mp3);
			chmod($mp3_file, 0777);
			$result['status'] = 'success';
			$result['message'] = 'Đăng nhạc mới thành công: '.$mp3_name;
			$result['mp3_name'] = $mp3_name;
		}else{
			$result['status'] = 'error';
			$result['message'] = 'Bài hát này đã được đăng';
		}
		echo json_encode($result);
	}
	public function delete($mp3_name){
		$mp3_file = __DIR__.'/files/'.$mp3_name.'.mp3';
		if (unlink($mp3_file)){
			$result['status'] = 'success';
		}else{
			$result['status'] = 'error';
		}
		echo json_encode($result);
	}
	public function remove_http($url){
		$disallowed = array('http://', 'https://');
		foreach($disallowed as $d) {
			if(strpos($url, $d) === 0) {
				$url = str_replace('www.', '', $url);
				$url = str_replace($d, $d.'m.', $url);
				return $url;
			}
		}
		return $url;
	}
	public function vn_str_filter($str){ 
		$unicode = [ 'a'=>
				'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ', 'd'=>
				'đ', 'e'=>
				'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ', 'i'=>
				'í|ì|ỉ|ĩ|ị', 'o'=>
				'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ', 'u'=>
				'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự', 'y'=>
				'ý|ỳ|ỷ|ỹ|ỵ', 'A'=>
				'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ', 'D'=>
				'Đ', 'E'=>
				'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ', 'I'=>
				'Í|Ì|Ỉ|Ĩ|Ị', 'O'=>
				'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ', 'U'=>
				'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự', 'Y'=>
				'Ý|Ỳ|Ỷ|Ỹ|Ỵ'];
		foreach($unicode as $nonUnicode=> $uni){ 
			$str = preg_replace("/($uni)/i", $nonUnicode, $str); 
		} 
		return $str; 
	}

}

