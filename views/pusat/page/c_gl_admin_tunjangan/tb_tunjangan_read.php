<!doctype html>
<html>
    <head>
        <title>harviacode.com - codeigniter crud generator</title>
        <link rel="stylesheet" href="<?php echo base_url('assets/bootstrap/css/bootstrap.min.css') ?>"/>
        <style>
            body{
                padding: 15px;
            }
        </style>
    </head>
    <body>
        <h2 style="margin-top:0px">Tb_tunjangan Read</h2>
        <table class="table">
	    <tr><td>Nama Tunjangan</td><td><?php echo $nama_tunjangan; ?></td></tr>
	    <tr><td>Nominal</td><td><?php echo $nominal; ?></td></tr>
	    <tr><td>Ket Tunjangan</td><td><?php echo $ket_tunjangan; ?></td></tr>
	    <tr><td>Tgl Ins</td><td><?php echo $tgl_ins; ?></td></tr>
	    <tr><td>Tgl Updt</td><td><?php echo $tgl_updt; ?></td></tr>
	    <tr><td>User Ins</td><td><?php echo $user_ins; ?></td></tr>
	    <tr><td>User Updt</td><td><?php echo $user_updt; ?></td></tr>
	    <tr><td></td><td><a href="<?php echo site_url('c_gl_admin_tunjangan') ?>" class="btn btn-default">Cancel</a></td></tr>
	</table>
        </body>
</html>