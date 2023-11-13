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
        <h2 style="margin-top:0px">Tb_tunjangan <?php echo $button ?></h2>
        <form action="<?php echo $action; ?>" method="post">
	    <div class="form-group">
            <label for="varchar">Nama Tunjangan <?php echo form_error('nama_tunjangan') ?></label>
            <input type="text" class="form-control" name="nama_tunjangan" id="nama_tunjangan" placeholder="Nama Tunjangan" value="<?php echo $nama_tunjangan; ?>" />
        </div>
	    <div class="form-group">
            <label for="float">Nominal <?php echo form_error('nominal') ?></label>
            <input type="text" class="form-control" name="nominal" id="nominal" placeholder="Nominal" value="<?php echo $nominal; ?>" />
        </div>
	    <div class="form-group">
            <label for="ket_tunjangan">Ket Tunjangan <?php echo form_error('ket_tunjangan') ?></label>
            <textarea class="form-control" rows="3" name="ket_tunjangan" id="ket_tunjangan" placeholder="Ket Tunjangan"><?php echo $ket_tunjangan; ?></textarea>
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
	    <input type="hidden" name="id_tunjangan" value="<?php echo $id_tunjangan; ?>" /> 
	    <button type="submit" class="btn btn-primary"><?php echo $button ?></button> 
	    <a href="<?php echo site_url('c_gl_admin_tunjangan') ?>" class="btn btn-default">Cancel</a>
	</form>
    </body>
</html>