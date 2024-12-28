<?php
// Include the database connection
include 'db_connect.php';

// Get the search filters from the request (these would come from the form)
$departure_airport_id = isset($_POST['departure_airport_id']) ? $_POST['departure_airport_id'] : '';
$arrival_airport_id = isset($_POST['arrival_airport_id']) ? $_POST['arrival_airport_id'] : '';
$departure_date = isset($_POST['departure_date']) ? $_POST['departure_date'] : '';
$trip_type = isset($_POST['trip_type']) ? $_POST['trip_type'] : 'one-way'; // 'one-way' or 'round-trip'

// Build the SQL query based on the filters
$query = "SELECT f.*, a.airlines, a.logo_path 
          FROM flight_list f 
          INNER JOIN airlines_list a ON f.airline_id = a.id 
          WHERE f.departure_datetime >= NOW()"; // Only show future flights

// Add filter conditions based on search form data
if ($departure_airport_id != '') {
    $query .= " AND f.departure_airport_id = $departure_airport_id";
}

if ($arrival_airport_id != '') {
    $query .= " AND f.arrival_airport_id = $arrival_airport_id";
}

if ($departure_date != '') {
    // Ensure the departure date is correctly formatted (assuming 'Y-m-d' format from the form)
    $query .= " AND DATE(f.departure_datetime) = '$departure_date'";
}

// For round-trip or one-way logic
if ($trip_type == 'round-trip') {
    // Implement logic for round trip (e.g., flights that return on the same date or later)
    $query .= " AND f.return_datetime >= '$departure_date'"; // Example: You can add return datetime logic here
}

// Execute the query
$qry = $conn->query($query);
?>

<div class="container-fluid">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header">
				<large class="card-title">
					<b>Flight List</b>
				</large>
				<button class="btn btn-primary btn-block col-md-2 float-right" type="button" id="new_flight"><i class="fa fa-plus"></i> New Flight</button>
			</div>
			<div class="card-body">
				<table class="table table-bordered" id="flight-list">
					<colgroup>
						<col width="10%">
						<col width="35%">
						<col width="10%">
						<col width="10%">
						<col width="10%">
						<col width="10%">
						<col width="15%">
					</colgroup>
					<thead>
						<tr>
							<th class="text-center">Date</th>
							<th class="text-center">Information</th>
							<th class="text-center">Seats</th>
							<th class="text-center">Booked</th>
							<th class="text-center">Available</th>
							<th class="text-center">Price</th>
							<th class="text-center">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php
							$airport = $conn->query("SELECT * FROM airport_list ");
							while($row = $airport->fetch_assoc()){
								$aname[$row['id']] = ucwords($row['airport'].', '.$row['location']);
							}

							// Display the results of the query
							while($row = $qry->fetch_assoc()):
								$booked = $conn->query("SELECT * FROM booked_flight where flight_id = ".$row['id'])->num_rows;
						?>
						 <tr>
						 	<td><?php echo date('M d,Y',strtotime($row['date_created'])) ?></td>
						 	<td>
						 		<div class="row">
						 			<div class="col-sm-4">
						 				<img src="../assets/img/<?php echo $row['logo_path'] ?>" alt="" class="btn-rounder badge-pill">
						 			</div>
						 			<div class="col-sm-6">
						 				<p>Airline :<b><?php echo $row['airlines'] ?></b></p>
						 				<p><small>Airline :<b><?php echo $row['airlines'] ?></small></b></p>
						 				<p><small>Location :<b><?php echo $aname[$row['departure_airport_id']].' - '.$aname[$row['arrival_airport_id']] ?></small></b></p>
						 				<p><small>Departure :<b><?php echo date('M d,Y h:i A',strtotime($row['departure_datetime'])) ?></small></b></p>
						 				<p><small>Arrival :<b><?php echo date('M d,Y h:i A',strtotime($row['arrival_datetime'])) ?></small></b></p>
						 			</div>
						 		</div>
						 	</td>
						 	<td class="text-right"><?php echo $row['seats'] ?></td>
						 	<td class="text-right"><?php echo $booked ?></td>
						 	<td class="text-right"><?php echo $row['seats'] - $booked ?></td>
						 	<td class="text-right"><?php echo number_format($row['price'],2) ?></td>
						 	<td class="text-center">
						 			<button class="btn btn-outline-primary btn-sm edit_flight" type="button" data-id="<?php echo $row['id'] ?>"><i class="fa fa-edit"></i></button>
						 			<button class="btn btn-outline-danger btn-sm delete_flight" type="button" data-id="<?php echo $row['id'] ?>"><i class="fa fa-trash"></i></button>
						 	</td>
						 </tr>
						<?php endwhile; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<style>
	td p {
		margin:unset;
	}
	td img {
	    width: 8vw;
	    height: 12vh;
	}
	td{
		vertical-align: middle !important;
	}
</style>	

<script>
	$('#flight-list').dataTable()
	$('#new_flight').click(function(){
		uni_modal("New Flight","manage_flight.php",'mid-large')
	})
	$('.edit_flight').click(function(){
		uni_modal("Edit Flight","manage_flight.php?id="+$(this).attr('data-id'),'mid-large')
	})
	$('.delete_flight').click(function(){
		_conf("Are you sure to delete this Flight?","delete_flight",[$(this).attr('data-id')])
	})
function delete_flight($id){
		start_load()
		$.ajax({
			url:'ajax.php?action=delete_flight',
			method:'POST',
			data:{id:$id},
			success:function(resp){
				if(resp==1){
					alert_toast("Flight successfully deleted",'success')
					setTimeout(function(){
						location.reload()
					},1500)
				}
			}
		})
	}
</script>
