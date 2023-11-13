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
        <h2 style="margin-top:0px">Tb_jam_kerja <?php echo $button ?></h2>
        <form action="<?php echo $action; ?>" method="post">
	    <div class="form-group">
            <label for="varchar">Nama Jam <?php echo form_error('nama_jam') ?></label>
            <input type="text" class="form-control" name="nama_jam" id="nama_jam" placeholder="Nama Jam" value="<?php echo $nama_jam; ?>" />
        </div>
	    <div class="form-group">
            <label for="varchar">Jam Masuk <?php echo form_error('jam_masuk') ?></label>
            <input type="text" class="form-control" name="jam_masuk" id="jam_masuk" placeholder="Jam Masuk" value="<?php echo $jam_masuk; ?>" />
        </div>
	    <div class="form-group">
            <label for="varchar">Jam Keluar <?php echo form_error('jam_keluar') ?></label>
            <input type="text" class="form-control" name="jam_keluar" id="jam_keluar" placeholder="Jam Keluar" value="<?php echo $jam_keluar; ?>" />
        </div>
	    <div class="form-group">
            <label for="timestamp">Tgl Ins <?php echo form_error('tgl_ins') ?></label>
            <input type="text" class="form-control" name="tgl_ins" id="tgl_ins" placeholder="Tgl Ins" value="<?php echo $tgl_ins; ?>" />
        </div>
	    <div class="form-group">
            <label for="datetime">Tgl Updt <?php echo form_error('tgl_updt') ?></label>
            <input type="text" class="form-control" name="tgl_updt" id="tgl_updt" placeholder="Tgl Updt" value="<?php echo $tgl_updt; ?>" />
        </div>
	    <div class="form-group">
            <label for="varchar">User Ins <?php echo form_error('user_ins') ?></label>
            <input type="text" class="form-control" name="user_ins" id="user_ins" placeholder="User Ins" value="<?php echo $user_ins; ?>" />
        </div>
	    <div class="form-group">
            <label for="varchar">User Updt <?php echo form_error('user_updt') ?></label>
            <input type="text" class="form-control" name="user_updt" id="user_updt" placeholder="User Updt" value="<?php echo $user_updt; ?>" />
        </div>
	    <input type="hidden" name="id_jam" value="<?php echo $id_jam; ?>" /> 
	    <button type="submit" class="btn btn-primary"><?php echo $button ?></button> 
	    <a href="<?php echo site_url('c_gl_admin_jam_kerja') ?>" class="btn btn-default">Cancel</a>
	</form>
    </body>
</html>