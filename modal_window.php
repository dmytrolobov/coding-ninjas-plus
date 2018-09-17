<?php
	global $wpdb;
	
	
	$fargs = array(
		'numberposts' => -1,
		'post_type'   => 'freelancer'
	);
	
	$fposts = get_posts( $fargs );
	
	$freelancers = '<select name="freelancer" id="freelancer" class="form-control">';
	$freelancers .= '<option value="0">Select freelancer</option>';
	
	if ($fposts )
	foreach( $fposts as $fpost ){
		setup_postdata( $fpost );
		$count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = '_freelancer' AND meta_value = '" . $fpost->ID ."';" ) );
		if ( $count < 3 ) {
			$freelancers .= '<option value="' . $fpost->ID . '">' . $fpost->post_title . '</option>';		
		}
		wp_reset_postdata();
	}
	
	$freelancers .= '</select>';
	
?>

<div id="modal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Add new task</h4>
			</div>
      <div class="modal-body">
				
				<form id="add-new-task">
					<div class="form-group row">
						<div class="col-sm-2"></div>
						<label for="tasktitle" class="col-sm-2 col-form-label">Task title</label>
						<div class="col-sm-6">
							<input type="text" class="form-control" id="tasktitle" name="tasktitle" placeholder="Enter Task title">
						</div>
						<div class="col-sm-2"></div>
					</div>
					<div class="form-group row">
						<div class="col-sm-2"></div>
						<label for="freelancer" class="col-sm-2 col-form-label">Freelancer</label>
						<div class="col-sm-6">
							<?php echo $freelancers;?>
						</div>
						<div class="col-sm-2"></div>
					</div>
					<div class="form-group row">
					<div class="col-sm-4"></div>
					<div class="col-sm-6">
						<button type="button" class="btn btn-primary">Add</button>
					</div>
					<div class="col-sm-2"></div>
					</div>
				</form>
				
				
				
        
			</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
