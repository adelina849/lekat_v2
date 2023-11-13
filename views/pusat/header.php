    <style>	
		@media (max-width:960px) 
		{
			#img_logo 
			{
				width: 30%;
			}
		}
	</style>
	
	<script type="text/javascript">
		var htmlobjek;
		$(document).ready(function()
		{	
		
			/*PANGGIL DATA PERTAMA*/
			//alert("BISA");
				
				/*TAMPILKAN NOTIFIKASI*/
					var cari = $('#cari').val();
					$.ajax({type: "POST", url: "<?php echo base_url();?>gl-admin-notofikasi-pasien/", data:{
						cari:""
					}, success:function(data)
					{
						if(data!=0)
						{
							$('#notifikasi_pasien').html(data);
						} 
						else
						{
							
						}
					}
					});
				/*TAMPILKAN NOTIFIKASI*/
				
				/*AMBIL NILAI AWAL NOTIF PASIEN*/
					var jum_notif_pasien_sound = $('#jum_notif_pasien_awal').val();
					$.ajax({type: "POST", url: "<?php echo base_url();?>gl-admin-notofikasi-pasien-sound/", data:{
						cari:""
					}, success:function(data)
					{
						if(data!=0)
						{
							$('#jum_notif_pasien_awal').val(data); //LANGSUNG ISI SAJA KARENA AWAL
						} 
						else
						{
							
						}
					}
					});
				/*AMBIL NILAI AWAL NOTIF PASIEN*/
				
				/*Notifikasi Sum Transaksi*/
					$.ajax({type: "POST", url: "<?php echo base_url();?>gl-admin-notofikasi-sum-transaksi/", data:{
						cari:""
					}, success:function(data)
					{
						if(data!=0)
						{
							var arrStrId = data;
							var arrIdSplit = arrStrId.split("|");
							
							var pending = arrIdSplit[0];
							var pemeriksaan = arrIdSplit[1];
							var pembayaran = arrIdSplit[2];
							var sum_all = arrIdSplit[3];
							
							$('#sb_jum_tr').html(sum_all);
							$('#sb_pemeriksaan_dokter').html(pending);
							$('#sb_pembayaran').html(pemeriksaan);
							$('#sb_perawatan_lanjut').html(pembayaran);
							$('#sb_apoteker').html(pembayaran);
							$('#sb_status_therapist').html(pembayaran);
							
							if((sum_all*1) > 0)
							{
								$('#5_transaksi').attr('class', 'active treeview');
							}
							else
							{
								//$('#5_transaksi').attr('class', 'treeview');
							}
							
							if((pembayaran*1)>0)
							{
								$('#7_laporan').attr('class', 'active treeview');
								$('#71_laporan_general').attr('class', 'active treeview');
								$('#7110_laporan_general_apoteker').attr('class', 'active treeview');
							}
							else
							{
								/*
								$('#7_laporan').attr('class', 'treeview');
								$('#71_laporan_general').attr('class', 'treeview');
								$('#7110_laporan_general_apoteker').attr('class', 'treeview');
								*/
							}
						} 
						else
						{
							
						}
					}
					});
				/*Notifikasi Sum Transaksi*/
			
			/*PANGGIL DATA PERTAMA*/
			
			window.setInterval(function() /*Terus dipanggil setiap 5 detik*/
			{
			   //window.location.reload(1);
			   //alert("PANGGIL1");
			   
			   //$("#notifikasi_pasien").html("<img src='<?php echo base_url();?>assets/global/loading.gif'>Cek Data ...");

 
				/*TAMPILKAN NOTIFIKASI*/
					var cari = $('#cari').val();
					$.ajax({type: "POST", url: "<?php echo base_url();?>gl-admin-notofikasi-pasien/", data:{
						cari:""
					}, success:function(data)
					{
						if(data!=0)
						{
							$('#notifikasi_pasien').html(data);
						} 
						else
						{
							
						}
					}
					});
				/*TAMPILKAN NOTIFIKASI*/
				
				/*AMBIL NILAI AWAL NOTIF PASIEN*/
					var jum_notif_pasien_sound = $('#jum_notif_pasien_awal').val();
					$.ajax({type: "POST", url: "<?php echo base_url();?>gl-admin-notofikasi-pasien-sound/", data:{
						cari:""
					}, success:function(data)
					{
						if(data!=0)
						{
							if( (data*1) > (jum_notif_pasien_sound*1)  )
							{
								var soundBeep = $("#soundBeep")[0];
								soundBeep.play();
								$('#jum_notif_pasien_awal').val(data);
							}
							else
							{
								$('#jum_notif_pasien_awal').val(data);
							}
						} 
						else
						{
							
						}
					}
					});
				/*AMBIL NILAI AWAL NOTIF PASIEN*/
				
				/*Notifikasi Sum Transaksi*/
					$.ajax({type: "POST", url: "<?php echo base_url();?>gl-admin-notofikasi-sum-transaksi/", data:{
						cari:""
					}, success:function(data)
					{
						if(data!=0)
						{
							var arrStrId = data;
							var arrIdSplit = arrStrId.split("|");
							
							var pending = arrIdSplit[0];
							var pemeriksaan = arrIdSplit[1];
							var pembayaran = arrIdSplit[2];
							var sum_all = arrIdSplit[3];
							
							$('#sb_jum_tr').html(sum_all);
							$('#sb_pemeriksaan_dokter').html(pending);
							$('#sb_pembayaran').html(pemeriksaan);
							$('#sb_perawatan_lanjut').html(pembayaran);
							$('#sb_apoteker').html(pembayaran);
							$('#sb_status_therapist').html(pembayaran);
							
							if((sum_all*1) > 0)
							{
								$('#5_transaksi').attr('class', 'active treeview');
							}
							else
							{
								//$('#5_transaksi').attr('class', 'treeview');
							}
							
							if((pembayaran*1)>0)
							{
								$('#7_laporan').attr('class', 'active treeview');
								$('#71_laporan_general').attr('class', 'active treeview');
								$('#7110_laporan_general_apoteker').attr('class', 'active treeview');
							}
							else
							{
								/*
								$('#7_laporan').attr('class', 'treeview');
								$('#71_laporan_general').attr('class', 'treeview');
								$('#7110_laporan_general_apoteker').attr('class', 'treeview');
								*/
							}
						} 
						else
						{
							
						}
					}
					});
				/*Notifikasi Sum Transaksi*/
				
			   
			}, 8000);
			
		});
	</script>
	
	<header class="main-header">
		
		<audio id="soundBeep" preload="auto">
			<source src="<?php echo base_url();?>assets/global/eventually.mp3"></source>
			<source src="<?php echo base_url();?>assets/global/eventually.ogg"></source>
		</audio>
		<input type="hidden" id="jum_notif_pasien_awal"/>
	
        <!-- Logo -->
        <a href="<?=base_url();?>index.php/" class="logo">
          <!-- mini logo for sidebar mini 50x50 pixels -->
          <span class="logo-mini"><b><?php echo '<img id="img_logo"  width="50%" src="'.$this->session->userdata('ses_gnl_logo_aplikasi_thumb').'" />'; ?></b></span>
          <!-- logo for regular state and mobile devices -->
          <span class="logo-lg"><?php echo '<img id="img_logo"  width="50%" src="'.$this->session->userdata('ses_gnl_logo_aplikasi_besar').'" />'; ?></span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
          <!-- Sidebar toggle button-->
          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
			
				<!--<span id="notifikasi_pasien">-->
				<li class="dropdown messages-menu" id="notifikasi_pasien">
					<!-- UNTUK MENAMPUNG NOTIFIKASI PASIEN -->
				</li>
				<!--</span>-->
			
			
              <!-- User Account: style can be found in dropdown.less -->
              <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <img src="<?php echo $this->session->userdata('ses_avatar_url');?>" class="user-image" alt="User Image">
                  <span class="hidden-xs"><?php echo $this->session->userdata('ses_nama_karyawan');?></span>
                </a>
                <ul class="dropdown-menu">
                  <!-- User image -->
                  <li class="user-header">
                    <img src="<?php echo $this->session->userdata('ses_avatar_url');?>" class="img-circle" alt="User Image">
                    <p>
                      <?php echo $this->session->userdata('ses_nama_karyawan');?>
					  <br><?php echo $this->session->userdata('ses_nama_jabatan');?>
                      <small>Teradaftar Sejak <?php echo $this->session->userdata('ses_tgl_ins');?></small>
					  <br>
                    </p>
                  </li>
                  <!-- Menu Body -->
                  <!-- Menu Footer-->
                  <li class="user-footer">
                    <div class="pull-left">
                      <!--<a href="#" class="btn btn-default btn-flat" data-toggle="modal" data-target="#myModalProfile">Profile</a>-->
					  <a href="#" data-toggle="modal" data-target="#modal_profile" class="btn btn-default btn-flat" >Profile</a>
					  
					  <!--
					  <a href="<?=base_url();?>index.php/gl-admin-data-karyawan-detail/<?php echo $this->session->userdata('ses_id_karyawan');  ?>" class="btn btn-default btn-flat">Detail</a>
					  -->
                    </div>
					
					
					
                    <div class="pull-right">
                      <a href="<?=base_url();?>index.php/gl-admin-logout" class="btn btn-default btn-flat">Sign out</a>
                    </div>
                  </li>
                </ul>
              </li>
              <!-- Control Sidebar Toggle Button -->
              <li>
                <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
              </li>
            </ul>
          </div>
        </nav>
      </header>
	  
	  
	   
	
	  
	  
      
      