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
        <h2 style="margin-top:0px">Tb_upselling <?php echo $button ?></h2>
        <form action="<?php echo $action; ?>" method="post">
	    <div class="form-group">
            <label for="date">Tanggal <?php echo form_error('tanggal') ?></label>
            <input type="text" class="form-control" name="tanggal" id="tanggal" placeholder="Tanggal" value="<?php echo $tanggal; ?>" />
        </div>
	    <div class="form-group">
            <label for="varchar">Id Costumer <?php echo form_error('id_costumer') ?></label>
            <input type="text" class="form-control" name="id_costumer" id="id_costumer" placeholder="Id Costumer" value="<?php echo $id_costumer; ?>" />
        </div>
	    <div class="form-group">
            <label for="varchar">Id Produk <?php echo form_error('id_produk') ?></label>
            <input type="text" class="form-control" name="id_produk" id="id_produk" placeholder="Id Produk" value="<?php echo $id_produk; ?>" />
        </div>
	    <div class="form-group">
            <label for="varchar">Foto <?php echo form_error('foto') ?></label>
            <input type="text" class="form-control" name="foto" id="foto" placeholder="Foto" value="<?php echo $foto; ?>" />
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
	    <div class="form-group">
            <label for="varchar">Kode Kantor <?php echo form_error('kode_kantor') ?></label>
            <input type="text" class="form-control" name="kode_kantor" id="kode_kantor" placeholder="Kode Kantor" value="<?php echo $kode_kantor; ?>" />
        </div>
	    <input type="hidden" name="id_upselling" value="<?php echo $id_upselling; ?>" /> 
	    <button type="submit" class="btn btn-primary"><?php echo $button ?></button> 
	    <a href="<?php echo site_url('c_gl_admin_upselling') ?>" class="btn btn-default">Cancel</a>
	</form>
    </body>
</html>