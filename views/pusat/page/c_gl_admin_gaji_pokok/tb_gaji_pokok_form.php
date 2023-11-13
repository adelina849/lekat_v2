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
        <h2 style="margin-top:0px">Tb_gaji_pokok <?php echo $button ?></h2>
        <form action="<?php echo $action; ?>" method="post">
	    <div class="form-group">
            <label for="varchar">Id Karyawan <?php echo form_error('id_karyawan') ?></label>
            <input type="text" class="form-control" name="id_karyawan" id="id_karyawan" placeholder="Id Karyawan" value="<?php echo $id_karyawan; ?>" />
        </div>
	    <div class="form-group">
            <label for="date">Tanggal <?php echo form_error('tanggal') ?></label>
            <input type="text" class="form-control" name="tanggal" id="tanggal" placeholder="Tanggal" value="<?php echo $tanggal; ?>" />
        </div>
	    <div class="form-group">
            <label for="float">Besar Gaji <?php echo form_error('besar_gaji') ?></label>
            <input type="text" class="form-control" name="besar_gaji" id="besar_gaji" placeholder="Besar Gaji" value="<?php echo $besar_gaji; ?>" />
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
	    <input type="hidden" name="id_gaji_pokok" value="<?php echo $id_gaji_pokok; ?>" /> 
	    <button type="submit" class="btn btn-primary"><?php echo $button ?></button> 
	    <a href="<?php echo site_url('c_gl_admin_gaji_pokok') ?>" class="btn btn-default">Cancel</a>
	</form>
    </body>
</html>