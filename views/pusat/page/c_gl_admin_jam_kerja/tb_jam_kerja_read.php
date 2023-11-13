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
        <h2 style="margin-top:0px">Tb_jam_kerja Read</h2>
        <table class="table">
	    <tr><td>Nama Jam</td><td><?php echo $nama_jam; ?></td></tr>
	    <tr><td>Jam Masuk</td><td><?php echo $jam_masuk; ?></td></tr>
	    <tr><td>Jam Keluar</td><td><?php echo $jam_keluar; ?></td></tr>
	    <tr><td>Tgl Ins</td><td><?php echo $tgl_ins; ?></td></tr>
	    <tr><td>Tgl Updt</td><td><?php echo $tgl_updt; ?></td></tr>
	    <tr><td>User Ins</td><td><?php echo $user_ins; ?></td></tr>
	    <tr><td>User Updt</td><td><?php echo $user_updt; ?></td></tr>
	    <tr><td></td><td><a href="<?php echo site_url('c_gl_admin_jam_kerja') ?>" class="btn btn-default">Cancel</a></td></tr>
	</table>
        </body>
</html>