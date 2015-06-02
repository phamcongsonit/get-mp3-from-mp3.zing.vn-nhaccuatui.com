<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Music Manager</title>
	<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="styles.css">
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="col-md-6">
				<h3>Thêm nhạc mới</h3>
				<div class="input-group">
					<input type="text" class="form-control url">
					<span class="input-group-btn">
						<button class="btn btn-primary" id="download" type="button">Tải nhạc!</button>
					</span>
				</div><!-- /input-group -->
				<div class="input-group">
					<span>Bạn có thể đăng nhạc của: <b>http://www.nhaccuatui.com</b>, <b>http://mp3.zing.vn</b></span>
				</div>
				<div class="input-group" style="width:100%">
					<i class="fa fa-circle-o-notch fa-spin load-icon hide"></i>
					<p class="bg-warning alert"></p>
					<p class="bg-success alert"></p>
				</div>
			</div>
			<div class="col-md-6">
				<h3>Quản lý nhạc</h3>
				<ul class="manager">
				<?php
					$mp3_files = array_diff(scandir(__DIR__.'/files/'), ['..', '.']);
					foreach($mp3_files as $mp3_file):
					$mp3_file = preg_replace('/\\.[^.\\s]{3,4}$/', '', $mp3_file);
				?>
					<li name="<?php echo $mp3_file ?>">
						<p class="bg-primary">
							<i class="fa fa-music"></i> - 
							<span class="title"><?php echo $mp3_file ?></span>
							<span class="delete"><i class="fa fa-times"></i></span>
						</p>
					</li>
				<?php
					endforeach;
				?>
				</ul>
			</div>
		</div>
		
	</div>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
	<script type="text/javascript">
	(function($){
		$(document).on('click', '.delete', function(){
			_this = this;
			data = [];
			data.push({name: 'action', value: 'delete'});
			data.push({name: 'mp3_name', value: $(_this).closest("li").attr("name")});
			$.ajax({
				type: 'POST',
				url: 'handle.php',
				data: data
			})
			.success(function(data){
				if (data.status == 'success'){
					$(_this).closest("li").remove();
				}
			});	
		});
		
		$(".url").attr("placeholder", "http://mp3.zing.vn/bai-hat/Forever-Alone-JustaTee/ZW6UF98O.html");
		$("#download").click(function(){
			data = [];
			data.push({name: 'url', value: $('.url').val()});
			data.push({name: 'type', value: $('.radio-type:checked').val()});
			data.push({name: 'action', value: 'create'});
			$("#download").prop("disabled", true);
			$(".url").prop("disabled", true);
			$(".load-icon").removeClass("hide");
			$(".bg-warning.alert").hide(0);
			$(".bg-success.alert").hide(0);
			$.ajax({
				type: 'POST',
				url: 'handle.php',
				data: data
			})
			.success(function(data){
				if (data.status == 'success'){
					$(".bg-warning.alert").hide(200).text(data.message);
					$(".bg-success.alert").show(200).text(data.message);
					$(".manager").append('<li name="' + data.mp3_name + '"><p class="bg-primary"><i class="fa fa-music"></i> - <span class="title">' +  data.mp3_name +  '</span><span class="delete"><i class="fa fa-times"></i></span></p></li>');
				}else{
					$(".bg-warning.alert").show(200).text(data.message);
					$(".bg-success.alert").hide(200).text(data.message);
				}
			})
			.done(function(){
				$(".load-icon").addClass("hide");
				$("#download").prop("disabled", false);
				$(".url").prop("disabled", false);
			})
		});
	})(jQuery)
	</script>
</body>
</html>