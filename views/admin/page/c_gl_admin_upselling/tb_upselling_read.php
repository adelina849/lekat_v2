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
        <h2 style="margin-top:0px">Tb_upselling Read</h2>
        <table class="table">
	    <tr><td>Tanggal</td><td><?php echo $tanggal; ?></td></tr>
	    <tr><td>Id Costumer</td><td><?php echo $id_costumer; ?></td></tr>
	    <tr><td>Id Produk</td><td><?php echo $id_produk; ?></td></tr>
	    <tr><td>Foto</td><td><?php echo $foto; ?></td></tr>
	    <tr><td>Tgl Ins</td><td><?php echo $tgl_ins; ?></td></tr>
	    <tr><td>Tgl Updt</td><td><?php echo $tgl_updt; ?></td></tr>
	    <tr><td>User Ins</td><td><?php echo $user_ins; ?></td></tr>
	    <tr><td>User Updt</td><td><?php echo $user_updt; ?></td></tr>
	    <tr><td>Kode Kantor</td><td><?php echo $kode_kantor; ?></td></tr>
	    <tr><td></td><td><a href="<?php echo site_url('c_gl_admin_upselling') ?>" class="btn btn-default">Cancel</a></td></tr>
	</table>
        </body>
</html>