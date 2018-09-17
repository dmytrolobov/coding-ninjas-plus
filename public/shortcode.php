<?php if ( ! defined( 'ABSPATH' ) ) exit; 
	$count_freelancers = wp_count_posts( 'freelancer' );
	$freelancers = $count_freelancers->publish;
	$count_tasks = wp_count_posts( 'task' );
	$tasks = $count_tasks->publish;
	
	?>
	
	<div class="col-lg-3 col-md-6">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-3">
						<i class="fa fa-users fa-5x"></i>
					</div>
					<div class="col-xs-9 text-right">
						<div class="huge"><?php echo $freelancers;?></div>
						<div>Freelancer</div>
					</div>
				</div>
			</div>			
		</div>
	</div>
	<div class="col-lg-3 col-md-6">
		<div class="panel panel-green">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-3">
						<i class="fa fa-tasks fa-5x"></i>
					</div>
					<div class="col-xs-9 text-right">
						<div class="huge"><?php echo $tasks;?></div>
						<div>Tasks</div>
					</div>
				</div>
			</div>			
		</div>
	</div>
	<div class="col-lg-3 col-md-6">		
	</div>
	<div class="col-lg-3 col-md-6">	
	</div>
</div>