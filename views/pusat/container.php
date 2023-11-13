<!DOCTYPE HTML>
    <head>
    	<meta http-equiv="content-type" content="text/html" />
    	<meta name="IMS Technology <?php echo date("y"); ?>" content="Dashboard Admin" />
		<meta content="width=device-width, initial-scale=1.0" name="viewport" />
		<meta content="width=device-width, initial-scale=1.0" name="viewport">
		<meta content="klinik, managemen, sistem, informasi, kesehatan" name="keywords">
		<meta content="Sistem Informasi dan Management Klinik IMS 1.0" name="description">
    
		<?php
			if(!empty($title))
			{
				echo'<title>'.$title.'</title>';
			}
			else
			{
				echo '<title>'.$this->session->userdata('ses_gnl_nama_aplikasi').'</title>';
			}
		?>
		<link rel="shortcut icon" href="<?php 
										echo $this->session->userdata('ses_gnl_logo_aplikasi_thumb');
									?>">
    	
		<!-- mobile settings -->
		<meta name="viewport" content="width=device-width, maximum-scale=1, initial-scale=1, user-scalable=0" />
        
        <!-- view source dll.-->
        <!--<script type="text/javascript">
        window.addEventListener("keydown",function(e){if(e.ctrlKey&&(e.which==65||e.which==66||e.which==67||e.which==73||e.which==80||e.which==83||e.which==85||e.which==86)){e.preventDefault()}});document.keypress=function(e){if(e.ctrlKey&&(e.which==65||e.which==66||e.which==67||e.which==73||e.which==80||e.which==83||e.which==85||e.which==86)){}return false}
        </script>
        <script type="text/javascript">
        document.onkeydown=function(e){e=e||window.event;if(e.keyCode==123||e.keyCode==18){return false}}
        </script>-->
        
        <!-- mengaktifkan javascript-->
        <!--<div align="center"><noscript>
           <div style="position:fixed; top:0px; left:0px; z-index:3000; height:100%; width:100%; background-color:#FFFFFF">
           <div style="font-family: Arial; font-size: 17px; background-color:#00bbf9; padding: 11pt;">Mohon aktifkan javascript pada browser untuk mengakses halaman ini!</div></div>
        </noscript></div>-->
        
        <!--kanan-->
        <!--<script type="text/javascript">
        function mousedwn(e){try{if(event.button==2||event.button==3)return false}catch(e){if(e.which==3)return false}}document.oncontextmenu=function(){return false};document.ondragstart=function(){return false};document.onmousedown=mousedwn
        </script>-->
        
        
        <!-- jQuery 2.1.4 -->
        <script src="<?php echo base_url();?>assets/adminlte/plugins/jQuery/jQuery-2.1.4.min.js"></script>
		
    </head>
    
    <body class="skin-purple sidebar-mini" onLoad="JavaScript:loadSearchHighlight();" style="background-color:##E50083;" >
	
        <div class="wrapper"> <!-- Buka | Untuk Wrapper/Container -->
            
            <!--<div> <!-- Buka | Untuk Header -->
                <?php
                    $this->load->view('pusat/header');
                ?>
            <!--</div> <!-- Tutup | Untuk Header -->
            
            
                <!-- Buka | Untuk Sidebar-->
                   <?php 
                        $this->load->view('pusat/sidebar');
                   ?> 
                <!-- Tutup | Untuk Sidebar -->
                
                
                
                    <?php 
                        $this->load->view('pusat/page/'.$page_content.'.html');
                        //$this->load->view('admin/page/'.$page_content);
                    ?>
                

            
            <div> <!-- Buka | Untuk Footer-->
                <?php 
                    $this->load->view('pusat/footer');
                ?>
            </div> <!-- Tutup | Untuk Footer -->
            
            <div> <!-- Buka | Sidebar control-->
                <?php 
                    $this->load->view('pusat/control_sidebar');
                ?>
            </div> <!-- Tutup | Sidebar Control -->
            
        </div> <!-- Tutup | Untuk Wrapper/Container -->
        
		
					<!-- Show MOdal - TABLE 1 -->
					<div class="modal fade" id="modal_profile" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
						<div class="modal-dialog">
							<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title" id="myModalLabel"><span id="keterangan_event2">Profile & Biodata Pengguna</span></h4>
							</div>
							<div class="modal-body">
								
								
								<!-- <input type="text" name="no_2" id="no_2" /> -->
								
								<center style="color:black;">
									<span id="avatar_profile"></span>
								</center>
								
								<!-- ISI BODY -->
								<div class="box">
								<!--<div class="box-header">-->
									
										<br/>
										  <div class="tab-pane" id="settings">
											<!-- <form class="form-horizontal"> -->
											
											<form role="form" action="<?php echo base_url();?>gl-profile-simpan" method="post" class="form-horizontal frm-input" enctype="multipart/form-data">
											
												<input type="hidden" name="prof_id_karyawan" id="prof_id_karyawan" value="<?php echo $this->session->userdata('ses_id_karyawan');?>"/>
											
											
												<div class="form-group">
												  <label class="col-sm-2 control-label" for="avatar"></label>
													<div class="col-sm-10">
													  <!--<span id="prof_img_edit"></span>-->
														<center>
															<img src="<?php echo $this->session->userdata('ses_avatar_url');?>" class="user-image" alt="User Image" style="width:25%;height:25%">
															
															<input type="file" id="prof_foto" name="prof_foto">
															<p class="help-block">Pilih untuk memasukan foto karyawan</p>
														</center>
													  
													</div>
												</div>
												<br/>
												<div class="form-group">
													<label for="prof_dept" class="col-sm-2 control-label">Divisi</label>
													<div class="col-sm-10">
													<input type="text" class="form-control" name="prof_dept" id="prof_dept" placeholder="*Departement/Divisi" value="<?php echo $this->session->userdata('ses_nama_dept');?>" disabled>
													</div>
												</div>
												<div class="form-group">
													<label for="Jabatan" class="col-sm-2 control-label">Jabatan</label>
													<div class="col-sm-10">
													<input type="text" class="form-control" name="prof_jabatan" id="prof_jabatan" placeholder="*Jabatan Karyawan" value="<?php echo $this->session->userdata('ses_nama_jabatan');?>" disabled>
													</div>
												</div>
												
												<div class="form-group">
													<label for="prof_no_karyawan" class="col-sm-2 control-label">NO</label>
													<div class="col-sm-10">
													<input type="text" class="form-control" name="prof_no_karyawan" id="prof_no_karyawan" placeholder="*No Karyawan" value="<?php echo $this->session->userdata('ses_no_karyawan');?>" disabled>
													</div>
												</div>
												
												<div class="form-group">
													<label for="prof_nik_karyawan" class="col-sm-2 control-label">NIK Karyawan</label>
													<div class="col-sm-10">
													<input type="text" class="form-control" name="prof_nik_karyawan" id="prof_nik_karyawan" placeholder="*NIK Karyawan" value="<?php echo $this->session->userdata('ses_nik_karyawan');?>" disabled>
													</div>
												</div>
												
												<div class="form-group">
													<label for="prof_nama_karyawan" class="col-sm-2 control-label">Nama</label>
													<div class="col-sm-10">
													<input type="text" class="form-control" name="prof_nama_karyawan" id="prof_nama_karyawan" placeholder="*Nama Karyawan"  value="<?php echo $this->session->userdata('ses_nama_karyawan');?>">
													</div>
												</div>
												
												<div class="form-group">
													<label for="prof_tmp_lahir" class="col-sm-2 control-label">Tempat Lahir</label>
													<div class="col-sm-10">
													<input type="text" class="form-control" name="prof_tmp_lahir" id="prof_tmp_lahir" placeholder="*Tempat Lahir" value="<?php echo $this->session->userdata('ses_tmp_lahir');?>">
													</div>
												</div>
												
												<div class="form-group">
													<label class="col-sm-2 control-label">Tanggal Lahir</label>
													<div class="col-sm-10">
														<div class="input-group date">
														  <div class="input-group-addon">
															<i class="fa fa-calendar"></i>
														  </div>
														  <input name="prof_tgl_lahir" id="prof_tgl_lahir" type="text" class="required form-control pull-right settingDate" alt="Tanggal Lahir" title="Tanggal Lahir" value="<?php echo $this->session->userdata('ses_tgl_lahir');?>" data-date-format="yyyy-mm-dd">
														</div>
													</div>
													<!-- /.input group -->
												</div>
												
												<div class="form-group">
												  <label class="col-sm-2 control-label" for="prof_kelamin">Jenis Kelamin</label>
													<div class="col-sm-10">
													  <select name="prof_kelamin" id="prof_kelamin" class="required form-control select2" title="Jenis Kelamin">
															<option  value="<?php echo $this->session->userdata('ses_kelamin');?>"><?php echo $this->session->userdata('ses_kelamin');?></option>
															<option value="LAKI-LAKI">LAKI-LAKI</option>
															<option value="PEREMPUAN">PEREMPUAN</option>
													   </select>
													</div>
												</div>
												
												<div class="form-group">
													<label class="col-sm-2 control-label" for="prof_pnd">Gelar Pendidikan</label>
													<div class="col-sm-10">
														<input type="text" id="prof_pnd" name="prof_pnd"  maxlength="25" class="required form-control" size="35" alt="Gelar Pendidikan" title="Gelar Pendidikan" placeholder="*Gelar Pendidikan" value="<?php echo $this->session->userdata('ses_pnd');?>"/>
													</div>
												</div>
												
												<div class="form-group">
												  <label class="col-sm-2 control-label" for="prof_tlp">No Telpon/Hp</label>
													<div class="col-sm-10">
														<input type="text" id="prof_tlp" name="prof_tlp"  maxlength="25" onkeypress="return isNumberKey(event)" class="required form-control" size="35" alt="No Telpon/Hp" title="No Telpon/Hp" placeholder="*No Telpon/Hp" value="<?php echo $this->session->userdata('ses_tlp');?>"/>
													</div>
												</div>
												
												<div class="form-group">
												  <input type="hidden" id="prof_cek_email" name="prof_cek_email" />
												  <label class="col-sm-2 control-label" for="prof_email">Email</label>
													<div class="col-sm-10">
														<input type="text" id="prof_email" name="prof_email"  maxlength="35" class="email form-control" size="35" alt="Email Harus Valid" title="Email Harus Valid" placeholder="Email Harus Valid"  value="<?php echo $this->session->userdata('ses_email');?>"/> <span id="prof_pesan2"></span>
													</div>
												</div>
												
												<div class="form-group">
												  <label class="col-sm-2 control-label" for="prof_sts_nikah">Status Pernikahan</label>
													<div class="col-sm-10">
														  <select name="prof_sts_nikah" id="prof_sts_nikah" class="required form-control select2" title="Status Pernikahan">
																<option  value="<?php echo $this->session->userdata('ses_sts_nikah');?>"> <?php echo $this->session->userdata('ses_sts_nikah');?></option>
																<option value="LAJANG">LAJANG</option>
																<option value="MENIKAH">MENIKAH</option>
																<option value="DUDA">DUDA</option>
																<option value="JANDA">JANDA</option>
														   </select>
													</div>
												</div>
												
												<div class="form-group">
												  <label class="col-sm-2 control-label" for="prof_alamat">Alamat Lengkap</label>
													<div class="col-sm-10">
														<textarea name="prof_alamat" id="prof_alamat" class="required form-control" title="Isi dengan alamat lengkap beserta RT dan RW" placeholder="*Isi dengan alamat lengkap beserta RT dan RW"><?php echo $this->session->userdata('ses_alamat');?></textarea>
													</div>
												</div>
												
												<div class="box box-warning collapsed-box box-solid">
													<div class="box-header with-border">
														<h3 class="box-title">Ubah Pengguna & Password</h3>
														<div class="box-tools pull-right">
															<button class="btn btn-box-tool" data-widget="collapse"><i id="icon_form" class="fa fa-plus"></i></button>
														</div><!-- /.box-tools -->
													</div><!-- /.box-header -->
													<div class="box-body">
												
														<div class="form-group" style="color:red;">
															<label for="prof_user" class="col-sm-2 control-label">USER</label>
															<div class="col-sm-10">
															<input type="text" class="form-control" name="prof_user" id="prof_user" placeholder="*Jangan diisi/biarkan saja jika tidak ingin merubah"  value="<?php echo $this->session->userdata('ses_user_admin');?>">
															</div>
														</div>
														
														<div class="form-group" style="color:red;">
															<label for="prof_pass" class="col-sm-2 control-label">PASSWORD</label>
															<div class="col-sm-10">
															<input type="password" class="form-control" name="prof_pass" id="prof_pass" placeholder="*Jangan diisi jika tidak ingin merubah"  value="">
															</div>
														</div>
														
														<div class="form-group" style="color:red;">
															<label for="prof_pass2" class="col-sm-2 control-label">KONFIRMASI PASSSWORD</label>
															<div class="col-sm-10">
															<input type="password" class="form-control" name="prof_pass2" id="prof_pass2" placeholder="*Jangan diisi jika tidak ingin merubah"  value="">
															</div>
														</div>
													</div>
												</div>
											
												<div class="form-group">
													<!--
													<div class="col-sm-offset-2 col-sm-10">
														<button type="submit" class="btn btn-danger" onclick="simpan_proses_lamaran()">Submit</button>
													</div>
													-->
													<!--
													<div class="box-footer">
														<button type="submit" class="btn btn-danger" onclick="simpan_proses_lamaran()">Submit</button>
													</div>
													-->
												</div>
											
										  </div>
										  
										 
									
									
								<!--</div> -->
								<!-- ISI BODY -->
								
								
								</div>
							<div class="modal-footer">
								<!--<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>-->
								<!-- <button type="button" class="btn btn-primary">Save changes</button> -->
								
								<!--
								<button type="submit" class="btn btn-primary" onclick="simpan_update_prodile_show_modal()">Submit</button>
								-->
								
								<button type="submit" class="btn btn-primary" >Submit</button>
							</div>
							</form>
							</div>
						</div>
						</div>
					</div>
				<!-- Show MOdal - TABLE 1-->
				
				<script type='text/javascript'>
					function simpan_update_prodile_show_modal()
					{
					
						var prof_user = $("#prof_user").val();
						var prof_pass = $("#prof_pass").val();
						var prof_pass2 = $("#prof_pass2").val();
						
						//alert("HALLO");
						
						if(prof_pass2 == "")
						{
						}
						else
						{
							if(prof_pass == prof_pass2)
							{
								//alert("Password sama !");
							}
							else
							{
								alert("Kolom password tidak sama Kolom Konfirmasi Password sehingga username dan password tidak akan berubah, tapi data yang lain akan berubah !");
							}
						}
						
						//alert("HALLO");
					}
				</script>
         
        <!-- AdminLTE for demo purposes -->
        <script src="<?php echo base_url();?>assets/adminlte/dist/js/demo.js"></script>
        <!-- page script -->
        
    </body>
</html>