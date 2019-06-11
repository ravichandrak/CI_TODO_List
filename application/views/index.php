<!DOCTYPE html>
<html lang="">
	<head>
		<meta charset="utf-8"/>
		<meta name="author" content="Ravichandra"/>
		<meta name="creator" content="Ravichandra"/>
		<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
		<meta name="viewport" content="width=device-width, initial-scale=1"/>
		<title><?php echo $title; ?></title>

		<link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet"/>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"/>
		<link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/sweetalert.css'); ?>"/>
		<link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/main.css'); ?>"/>
	</head>
	<body>
		<?php
			$compltdList = [];
			$pendingList = [];
			
			if (count($todos) > 0) {
				foreach ($todos as $todo) {
					if ($todo->status == 1) {
						$compltdList[] = $todo;
					}
					else {
						$pendingList[] = $todo;
					}
				}
			} 
		?>
	
		<div class="container-fluid">
			<div class="row" style="text-align: center;margin-top: 15px;">
				<h1><?php echo $pageHeader; ?></h1>
				<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4" style="float: none;display: inline-block;margin-top:20px;">
					<div id="input-panel">
						<form name="todo-form" id="todo-from">
							<input name="todo-input" type="text" placeholder="ADD SOMETHING TO DO" autocomplete="off"/>
						</form>
					</div>
					<small id="log" class="hidden">...</small>
					
					<div class="panel panel-primary pendingPanel">
						<div class = "panel-heading">
							<h3 class="panel-title text-left">Pending Lists</h3>
						</div>
						<div class="panel-body">
							<ul id="todo-container" class="pendingList">
								<?php if (count($pendingList) > 0) { ?>
								<?php foreach ($pendingList as $todo) { ?>
								<li data-id="<?php echo $todo->id; ?>">
									<span data-id="<?php echo $todo->id; ?>"><?php echo $todo->name; ?></span>
									<div class="action">
										<button data-toggle="tooltip" data-title="Edit" class="edit-btn" data-id="<?php echo $todo->id; ?>"><i class="glyphicon glyphicon-pencil"></i></button>
										<button data-toggle="tooltip" data-title="Done" class="done-btn" data-id="<?php echo $todo->id; ?>"><i class="glyphicon glyphicon-ok"></i></button>
									</div>
								</li>
								<?php } } ?>
								
								<span class="noPenList" id="nothing" style="<?php echo (count($pendingList) > 0) ? 'display:none' : 'display:block'; ?>;color:#999">No pending list found.</span>
							</ul>
						</div>
					</div>
										
					<div class="panel panel-success">
						<div class = "panel-heading">
							<h3 class="panel-title text-left">Completed Lists</h3>
						</div>
						<div class="panel-body">
							<ul id="todo-container" class="completedList">
								<?php if (count($compltdList) > 0) { ?>
								<?php foreach ($compltdList as $todo) { ?>
								<li data-id="<?php echo $todo->id; ?>">
									<span data-id="<?php echo $todo->id; ?>"><?php echo $todo->name; ?></span>									
								</li>
								<?php } } ?>
								
								<span class="noCmpList" id="nothing" style="<?php echo (count($compltdList) > 0) ? 'display:none' : 'display:block'; ?>;color:#999">No completed list found.</span>
							</ul>
						</div>
					</div>
										
				</div>
			</div>
		</div>

		<script type="text/javascript" src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="<?php echo base_url('assets/js/sweetalert.min.js'); ?>"></script>
		<script type="text/javascript">
			
		$(function() {

			var uri = '<?php echo base_url(); ?>'
			$('[data-toggle=tooltip]').tooltip();

			$('#todo-from').submit(function(e) {
				e.preventDefault();
				var todoval = $('input[name=todo-input]').val();
				if (todoval == '') {
					alert('Enter todo value >:(');
					$('input[name=todo-input]').focus();
					return false;
				}

				$.ajax({
					'type': "POST",
					data: { todo: todoval },
					url: uri+'insert',
					dataType: "json",
					beforeSend: function(e) {
						$('#log').removeClass("hidden").html('inserting..')
					},
					error: function(error) {
						$('#log').removeClass("hidden").html('something wrong');
					},
					success: function(response) {
						resetLog();
						
						$('input[name=todo-input]').val('');
						$('.pendingList').append("<li data-id="+ response.id +"><span data-id="+ response.id +">"+ response.name +"</span>"+
														"<div class=\"action\">"+
															"<button data-toggle=\"tooltip\" data-title=\"Edit\" class=\"edit-btn\" data-id="+ response.id +"><i class=\"glyphicon glyphicon-pencil\"></i></button>\r\n"+
															"<button data-toggle=\"tooltip\" data-title=\"Done\" class=\"done-btn\" data-id="+ response.id +"><i class=\"glyphicon glyphicon-ok\"></i></button>"+
														"</div>"+
													"</li>");
						$('[data-toggle=tooltip]').tooltip();
					}
				});
			});

			$('body').on('click','.done-btn',function(e) {
				
				var id = $(this).attr('data-id');
				
				if (typeof id == undefined) {
					alert("something wrong!!");
					return false;
				}

				$.ajax({
					type :"POST",
					url : uri + 'done',
					dataType : 'json',					
					data:{ id: id },
					beforeSend: function(e) {
						$("ul.pendingList li[data-id='"+ id +"']").css('background-color','rgba(120, 174, 223,0.2)');
						$("#log").removeClass("hidden").html("loading..");
					},
					error : function(error) {
						$('#log').removeClass("hidden").html('something wrong');
					},
					success : function(response) {
						
						var listName = response.name;
						
						$("ul.pendingList li[data-id='"+ id +"']").fadeOut(300);
						
						setTimeout(function() {
							$("ul.pendingList li[data-id='"+ id +"']").remove();
						}, 500);
						
						resetLog();
						checkifempty();
												
						$('.noCmpList').css('display','none');						
						$('ul.completedList').append("<li data-id="+ id +"><span data-id="+ id +">"+ listName +"</span>");
						
					}
				});
			})

			$('body').on('click','.edit-btn',function(e) {
				var id = $(this).attr('data-id');
				if(typeof id == undefined) {
					alert("something wrong!!");
					return false;
				}

				$.ajax({
					type: 'POST',
					url: uri+'edit',
					data: { id:id },
					dataType: 'json',
					beforeSend: function(e) {
						$("#log").removeClass("hidden").html("loading..");
					},
					error : function(error) {
						$('#log').removeClass("hidden").html('something wrong');
					},
					success : function(response) {
						resetLog();
						
						swal({
						  title: "Edit",
						  text: "What will you do then?",
						  type: "input",
						  showCancelButton: true,
						  closeOnConfirm: false,
						  showLoaderOnConfirm: true,
						  animation: "slide-from-top",
						  inputValue: response.name,
						  inputPlaceholder: "Do something"
						},
						function(inputValue) {
							if (inputValue === "" || inputValue === false) {
							    return false;
							}
							update(id, inputValue);
						});
					}
				});
			});

			var update = function(id, name){
				$.ajax({
					type: "POST",
					url: uri + 'update',
					data: { id : id, todo:name },
					dataType:'json',
					beforeSend: function(e) {
						$("#log").removeClass("hidden").html("updating..");
					},
					error : function(error) {
						$("#log").removeClass("hidden").html("something wrong");
					},
					success :function(response) {
						resetLog();
						$("ul.pendingList li[data-id='"+ response.id +"'] span").html(response.name);
						setTimeout(function(e){
							swal.close();
						}),300;
					}
				});				
			}
			
			var resetLog = function() {
				$('#log').html('...').addClass("hidden");
			}
			
			var checkifempty = function() {
				$.get(uri+'countTodos', function(response) {
					response = $.parseJSON(response);
					if (response[0] == undefined) {
						$('.noPenList').css('display','block');
						//$('.pendingPanel').hide();						
					}
				})
			}
		});

		</script>
	</body>
</html>