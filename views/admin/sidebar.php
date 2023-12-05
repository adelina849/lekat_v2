        <style><!-- 
        SPAN.searchword { background-color:yellow; }
        // -->
        </style>
        <script src="<?=base_url();?>assets/global/js/searchhi.js" type="text/javascript" language="JavaScript"></script>
        <script language="JavaScript"><!--
        function loadSearchHighlight()
        {
          SearchHighlight();
          document.searchhi.h.value = searchhi_string;
          if( location.hash.length > 1 ) location.hash = location.hash;
        }
        // -->
        </script>
    <!-- Left side column. contains the logo and sidebar -->
      <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">
          <!-- Sidebar user panel -->
          <div class="user-panel">
            <div class="pull-left image">
              <img src="<?php echo $this->session->userdata('ses_avatar_url');?>" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
              	<p>
					<?php echo substr($this->session->userdata('ses_nama_karyawan'),0,18);?>
					<br/>
					<?php echo substr($this->session->userdata('ses_nama_karyawan'),18,50);?>
				</p>
              <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
          </div>
          <!-- search form -->
          <form method="get" name="searchhi" class="sidebar-form" action="#">
            <div class="input-group">
              <input type="text" name="h" onkeyup="oWhichSubmit(this)" class="form-control" placeholder="Search...">
              <span class="input-group-btn">
                <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i></button>
              </span>
            </div>
          </form>
          <!-- /.search form -->
          <!-- sidebar menu: : style can be found in sidebar.less -->
          <ul class="sidebar-menu">
			<!-- CEK HAK AKSES FROM DATABASE -->
				<?php
					/*$akses_group1 = $this->m_akun->get_hak_akses_group1($this->session->userdata('ses_id_jabatan'));
					$akses_group1_main_group = $this->m_akun->get_hak_akses_group1_main_group($this->session->userdata('ses_id_jabatan'));
					$akses_group1_main_group_sub_main = $this->m_akun->get_hak_akses_group1_main_group_sub_group($this->session->userdata('ses_id_jabatan'));*/
				?>
			<!-- CEK HAK AKSES FROM DATABASE -->
		  
            <li class="header">MAIN NAVIGATION</li>
            <!-- <li class="active treeview"> -->
			<li id="dashboard" class="treeview">
              <a href="<?=base_url()?>admin"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a>
            </li>
			
			<!-- CEK AKSES GROUP1 = 1 -->
			
				<li id="inputdata" class="treeview">
				  <a href="#">
					<i class="fa fa-laptop"></i> <span>Input Data (Basis Data)</span>
					<i class="fa fa-angle-left pull-right"></i>
				  </a>
				  <ul class="treeview-menu">
					
					<!-- CEK AKSES KARYAWAN -->
							<li id="input-data-karyawan">
							  <a href="#"><i class="fa fa-folder"></i> Input Karyawan <i class="fa fa-angle-left pull-right"></i></a>
							  
							  <ul class="treeview-menu">
								<!-- CEK AKSES JABATAN -->
									<li id="input-data-karyawan-kategori"><a href="<?=base_url()?>admin-jabatan"><i class="fa fa-edit"></i> Jabatan </a></li>
								<!-- CEK AKSES JABATAN -->
								
								<!-- CEK AKSES KARYAWAN -->
									<li id="input-data-karyawan-karyawan"><a href="<?=base_url()?>admin-karyawan"><i class="fa fa-users"></i> Karyawan </a></li>
								<!-- CEK AKSES KARYAWAN -->
							  </ul>
							</li>
					<!-- CEK AKSES KARYAWAN -->
					
					<!-- CEK AKSES KECMATAN -->
							<li id="input-data-kecamatan">
							  <a href="#"><i class="fa fa-folder"></i> Input Data Kecamatan <i class="fa fa-angle-left pull-right"></i></a>
							  <ul class="treeview-menu">
								<!-- CEK AKSES KECAMATAN -->
									<li id="input-data-kecamatan-kecamatan"><a href="<?=base_url()?>kecamatan"><i class="fa fa-edit"></i> Input Kecamatan </a></li>
								<!-- CEK AKSES KECAMATAN -->
							  
								<!-- CEK AKSES DESA -->
									<li id="input-data-kecamatan-desa"><a href="<?=base_url()?>kecamatan-list-desa"><i class="fa fa-edit"></i> Input Desa </a></li>
								<!-- CEK AKSES DESA -->
							  </ul>
							</li>
					<!-- CEK AKSES KECMATAN -->
					
					<!-- CEK AKSES KONTES -->
							<!--<li id="input-data-akun"><a href="<?=base_url()?>admin-nomor-akun"><i class="fa fa-edit"></i> Nomor Akun</a></li>-->
					<!-- CEK AKSES KONTES -->
					
					
				  </ul>
				</li>
			<!-- CEK AKSES GROUP1 = 1 -->
			
			
			<!-- CEK AKSES GROUP1 = 2 -->
				<li class="treeview" id="input-konfig-laporan">
				  <a href="#">
					<i class="fa fa-table"></i> <span>Pengaturan Laporan</span>
					<i class="fa fa-angle-left pull-right"></i>
				  </a>
				  <ul class="treeview-menu">
					
					<!-- PERIODE LAPORAN -->
						<li id="input-konfig-periode"><a href="<?=base_url()?>periode-laporan"><i class="fa fa-circle-o"></i>
							Periode Laporan
						</a></li>
					<!-- PERIODE LAPORAN -->
					
					<!-- CEK AKSES Kategori LAPORAN -->
							<li id="input-konfig-kategori"><a href="<?=base_url()?>kategori-laporan"><i class="fa fa-circle-o"></i> Kategori Laporan</a></li>
					<!-- CEK AKSES Kategori LAPORAN -->
					
					<!-- CEK AKSES JENIS LAPORAN -->
							<!--<li id="input-konfig-laporan-laporan"><a href="<?=base_url()?>jenis-laporan"><i class="fa fa-circle-o"></i> Jenis Laporan</a></li>-->
					<!-- CEK AKSES JENIS LAPORAN -->
					
					<!-- LAPORAN -->
						<li id="input-konfig-laporan-board"><a href="<?=base_url()?>board-laporan"><i class="fa fa-circle-o"></i> Jenis Laporan</a></li>
					<!-- LAPORAN -->
					
				  </ul>
				</li>
			<!-- CEK AKSES GROUP1 = 2 -->
			
			<!-- CEK AKSES HASIL LAPORAN-->
				<li class="treeview" id="hasil-laporan">
				  <a href="#">
					<i class="fa fa-share"></i> <span>Hasil Laporan</span>
					<i class="fa fa-angle-left pull-right"></i>
				  </a>
				  <ul class="treeview-menu">
					
					<!-- LAPORAN PERKECAMATAN -->
						<li id="hasil-laporan-laporan-kec"><a href="<?=base_url()?>laporan-kecamatan"><i class="fa fa-circle-o"></i>
							Perkecamatan
						</a></li>
					<!-- LAPORAN PERKECAMATAN -->
					
					<!-- LAPORAN ARSIP PERPERIODE-->
						<li id="hasil-laporan-periode"><a href="<?=base_url()?>laporan-per-periode"><i class="fa fa-circle-o"></i>
							Arsip Periode
						</a></li>
					<!-- LAPORAN ARSIP PERPERIODE-->
					
					<!-- LAPORAN AKUMULASI-->
						<li id="hasil-laporan-akum"><a href="<?=base_url()?>akumulasi-laporan-kecamatan"><i class="fa fa-circle-o"></i>
							Akumulasi
						</a></li>
					<!-- LAPORAN AKUMULASI-->
					
					<!-- LAPORAN AKUMULASI-->
						<li id="cetak-hasil-laporan-akum"><a href="<?=base_url()?>akumulasi-laporan-kecamatan-cetak"><i class="fa fa-circle-o"></i>
							Laporan Akumulasi
						</a></li>
					<!-- LAPORAN AKUMULASI-->
					
				  </ul>
				</li>
					
			<!-- CEK AKSES HASIL LAPORAN-->
			
			<!-- CEK AKSES GROUP1 = 6 AKUN-->
					<li id="aksesakun" class="treeview">
					  <a href="#">
						<i class="fa fa-share"></i> <span>Hak Akses dan Akun</span>
						<i class="fa fa-angle-left pull-right"></i>
					  </a>
					  <ul class="treeview-menu">
					  
						<!-- CEK AKSES AKUN -->
								<li id="akunakses-akun">
									<a href="<?=base_url()?>admin-akun"><i class="fa fa-circle-o"></i>Pemberian Akun</a>
								</li>
						<!-- CEK AKSES AKUN-->
						<!-- <li><a href="#"><i class="fa fa-circle-o"></i>Pemberian hak Akses</a></li> -->
					  </ul>
					</li>
			<!-- CEK AKSES GROUP1 = 6 AKUN-->
			
			<!-- CEK AKSES GROUP1 = 7 CATATAN-->
				<li id="dashboard" class="treeview">
					<a href="<?=base_url()?>gl-admin-list-catatan-laporan"><i class="fa fa-table"></i> <span>
																									Catatan Laporan
																									<small class="label pull-right bg-red" id="hitung_catatan">0</small>
																								</span></a>
				</li>
			<!-- CEK AKSES GROUP1 = 7 CATATAN-->
			
			<!-- CEK AKSES GROUP1 = 8 RANGKING-->
				<li id="dashboard" class="treeview">
					<a href="<?=base_url()?>gl-admin-list-rangking-kecamatan"><i class="fa fa-table"></i> <span>
																									Rangking Kecamatan
																								</span></a>
				</li>
				
				<li id="dashboard" class="treeview">
					<a href="<?=base_url()?>gl-admin-akumulasi-rangking-kecamatan"><i class="fa fa-table"></i> <span>
																									Akumulasi Rangking
																								</span></a>
				</li>
			<!-- CEK AKSES GROUP1 = 7 RANGKING-->
			
			
          </ul>
        </section>
        <!-- /.sidebar -->
      </aside>

<script type='text/javascript'>
	$(document).ready(function() {
	//function cnt_catatan()
	//{
			var ade = "ade";
			$.ajax({type: "POST", url: "<?php echo base_url();?>C_kec/hitung_catatan/", data: {
				ade:ade,
			}, success:function(data)
			{
				if(data!=0)
				{	
					$('#hitung_catatan').html(data);
				} 
				else
				{
					
				}
			}
			});
		
	//}
	});
</script>