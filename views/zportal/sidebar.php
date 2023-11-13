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
              <img src="<?php echo $this->session->userdata('ses_MUZ_AVATARLINK_public');?>" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
              <p><?php echo $this->session->userdata('ses_MUZ_NAMA_public');?></p>
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
		  <ul class="sidebar-menu" data-widget="tree">
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
			
			<li id="pembayaran" class="treeview">
              <a href="<?=base_url()?>admin"><i class="fa fa-laptop"></i> <span>Pembayaran</span></a>
            </li>
			
			<li id="pembayaran" class="treeview">
              <a href="<?=base_url()?>admin"><i class="fa fa-folder"></i> <span>Penyaluran</span></a>
            </li>
			
			<!-- CEK AKSES GROUP1 = 1 -->
			
				<?php
				if( ($this->session->userdata('group1_akses1') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
				{
				?>
				<li id="inputdata" class="treeview">
				  <a href="#">
					<i class="fa fa-laptop"></i> <span>Input Data (Basis Data)</span>
					<i class="fa fa-angle-left pull-right"></i>
				  </a>
				  <ul class="treeview-menu">
					
						<?php
						if( ($this->session->userdata('main_group_akses11') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="input-data-karyawan">
						  <a href="#"><i class="fa fa-folder"></i> Input Karyawan <i class="fa fa-angle-left pull-right"></i></a>
						  
						  <ul class="treeview-menu">
						  
								<?php
								if( ($this->session->userdata('sub_group_akses111') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-karyawan-kategori"><a href="<?=base_url()?>admin-jabatan"><i class="fa fa-edit"></i> Jabatan </a></li>
								<?php
								}
								?>
								
								<?php
								if( ($this->session->userdata('sub_group_akses114') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-karyawan-departemen"><a href="<?=base_url()?>admin-departemen"><i class="fa fa-edit"></i> Departemen </a></li>
								<?php
								}
								?>
							
								<?php
								if( ($this->session->userdata('sub_group_akses112') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-karyawan-karyawan"><a href="<?=base_url()?>admin-karyawan"><i class="fa fa-edit"></i> Karyawan </a></li>
								<?php
								}
								?>
							
								<?php
								if( ($this->session->userdata('sub_group_akses113') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="akunakses-akun">
									<a href="<?=base_url()?>admin-akun"><i class="fa fa-edit"></i>Pemberian Akun</a>
								</li>
								<?php
								}
								?>
							
						  </ul>
						</li>
						<?php
						}
						?>
					
					
						<?php
						if( ($this->session->userdata('main_group_akses12') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="input-data-menu-halaman">
						  <a href="#"><i class="fa fa-folder"></i> Menu dan Halaman <i class="fa fa-angle-left pull-right"></i></a>
						  <ul class="treeview-menu">
						  
								<?php
								if( ($this->session->userdata('sub_group_akses121') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-menu"><a href="<?=base_url()?>admin-menu"><i class="fa fa-edit"></i> Pengaturan Menu </a></li>
								<?php
								}
								?>
							
								<?php
								if( ($this->session->userdata('sub_group_akses122') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-halaman-halaman"><a href="<?=base_url()?>admin-halaman"><i class="fa fa-edit"></i> Pengaturan Halaman </a></li>
								<?php
								}
								?>
								
						  </ul>
						</li>
						<?php
						}
						?>
					
					
						<?php
						if( ($this->session->userdata('main_group_akses13') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="input-data-artikel">
						  <a href="#"><i class="fa fa-folder"></i> Artikel <i class="fa fa-angle-left pull-right"></i></a>
						  <ul class="treeview-menu">
						  
								<?php
								if( ($this->session->userdata('sub_group_akses131') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-artikel-kategori"><a href="<?=base_url()?>admin-kategori-artikel"><i class="fa fa-edit"></i> Kategori Artikel </a></li>
								<?php
								}
								?>
								
								<?php
								if( ($this->session->userdata('sub_group_akses132') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-artikel-artikel"><a href="<?=base_url()?>admin-artikel"><i class="fa fa-edit"></i> Artikel </a></li>
								<?php
								}
								?>
								
						  </ul>
						</li>
						<?php
						}
						?>
					
					
						<?php
						if( ($this->session->userdata('main_group_akses14') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="input-data-berita">
						  <a href="#"><i class="fa fa-folder"></i> Berita <i class="fa fa-angle-left pull-right"></i></a>
						  <ul class="treeview-menu">
						  
								<?php
								if( ($this->session->userdata('sub_group_akses141') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-berita-kategori"><a href="<?=base_url()?>admin-kategori-berita"><i class="fa fa-edit"></i> Kategori Berita </a></li>
								<?php
								}
								?>
							
								<?php
								if( ($this->session->userdata('sub_group_akses142') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-berita-berita"><a href="<?=base_url()?>admin-berita"><i class="fa fa-edit"></i> Berita </a></li>
								<?php
								}
								?>
						  </ul>
						</li>
						<?php
						}
						?>
					
						<?php
						if( ($this->session->userdata('main_group_akses15') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="input-data-galeri">
						  <a href="#"><i class="fa fa-folder"></i> Galeri <i class="fa fa-angle-left pull-right"></i></a>
						  <ul class="treeview-menu">
						  
								<?php
								if( ($this->session->userdata('sub_group_akses151') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-images">
								  <a href="#"><i class="fa fa-folder"></i> Foto <i class="fa fa-angle-left pull-right"></i></a>
								  <ul class="treeview-menu">
										<li id="input-data-images-kategori"><a href="<?=base_url()?>admin-kategori-images"><i class="fa fa-edit"></i> Kategori Foto </a></li>
									
										<li id="input-data-images-images"><a href="<?=base_url()?>admin-galeri-images"><i class="fa fa-edit"></i> Foto </a></li>
								  </ul>
								</li>
								<?php
								}
								?>
								
								<?php
								if( ($this->session->userdata('sub_group_akses152') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-video">
								  <a href="#"><i class="fa fa-folder"></i> Video <i class="fa fa-angle-left pull-right"></i></a>
								  <ul class="treeview-menu">
										<li id="input-data-video-kategori"><a href="<?=base_url()?>admin-kategori-video"><i class="fa fa-edit"></i> Kategori Video </a></li>
									
										<li id="input-data-video-video"><a href="<?=base_url()?>admin-galeri-video"><i class="fa fa-edit"></i> Video </a></li>
								  </ul>
								</li>
								<?php
								}
								?>
								
						  </ul>
						</li>
						<?php
						}
						?>
					
					
						<?php
						if( ($this->session->userdata('main_group_akses16') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="input-data-pos">
						  <a href="#"><i class="fa fa-folder"></i> Pos Pembayaran <i class="fa fa-angle-left pull-right"></i></a>
						  <ul class="treeview-menu">
						  
								<?php
								if( ($this->session->userdata('sub_group_akses161') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-pos-kategori"><a href="<?=base_url()?>admin-kategori-pos"><i class="fa fa-edit"></i> Kat.Pos Pembayaran </a></li>
								<?php
								}
								?>
								
								<?php
								if( ($this->session->userdata('sub_group_akses162') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-pos-pos"><a href="<?=base_url()?>admin-pos"><i class="fa fa-edit"></i> Pos Pembayaran </a></li>
								<?php
								}
								?>
						  </ul>
						</li>
						<?php
						}
						?>
					
						
						<?php
						if( ($this->session->userdata('main_group_akses17') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="input-data-kegiatan">
						  <a href="#"><i class="fa fa-folder"></i> Jadwal Kegiatan <i class="fa fa-angle-left pull-right"></i></a>
						  <ul class="treeview-menu">
						  
								<?php
								if( ($this->session->userdata('sub_group_akses171') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-jadwal-kegiatan"><a href="<?=base_url()?>admin-jadwal-kegiatan"><i class="fa fa-edit"></i> Kegiatan Kantor </a></li>
								<?php
								}
								?>
							
								<?php
								if( ($this->session->userdata('sub_group_akses172') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-jemput-zakat"><a href="<?=base_url()?>admin-jadwal-jemput-zakat"><i class="fa fa-edit"></i> Jemput Zakat </a></li>
								<?php
								}
								?>
						  </ul>
						</li>
						<?php
						}
						?>
					
						<?php
						if( ($this->session->userdata('main_group_akses18') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="input-data-pekerjaan">
						  <a href="#"><i class="fa fa-folder"></i> Pekerjaan <i class="fa fa-angle-left pull-right"></i></a>
						  <ul class="treeview-menu">
						  
								<?php
								if( ($this->session->userdata('sub_group_akses181') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-pekerjaan-perusahaan"><a href="<?=base_url()?>admin-perusahaan"><i class="fa fa-edit"></i> Perusahaan </a></li>
								<?php
								}
								?>
							
								<?php
								if( ($this->session->userdata('sub_group_akses182') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-pekerjaan-profesi"><a href="<?=base_url()?>admin-karyawan"><i class="fa fa-edit"></i> Profesi/Jabatan </a></li>
								<?php
								}
								?>
						  </ul>
						</li>
						<?php
						}
						?>
					
					
						<?php
						if( ($this->session->userdata('main_group_akses19') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="input-data-muzaqi">
						  <a href="#"><i class="fa fa-folder"></i> Muzaki/Munfiq <i class="fa fa-angle-left pull-right"></i></a>
						  <ul class="treeview-menu">
						  
								<?php
								if( ($this->session->userdata('sub_group_akses191') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-muzaqi-kategori"><a href="<?=base_url()?>admin-kategori-muzaqi"><i class="fa fa-edit"></i> Kat.Muzaki/Munfiq </a></li>
								<?php
								}
								?>
								
								<?php
								if( ($this->session->userdata('sub_group_akses193') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-muzaqi-barcode"><a href="<?=base_url()?>admin-muzaqi-barcode"><i class="fa fa-edit"></i> Barcode Muzaqi </a></li>
								<?php
								}
								?>
							
								<?php
								if( ($this->session->userdata('sub_group_akses192') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-muzaqi-muzaqi"><a href="<?=base_url()?>admin-muzaqi"><i class="fa fa-edit"></i> Muzaki/Munfiq </a></li>
								<?php
								}
								?>
						  </ul>
						</li>
						<?php
						}
						?>
					
						<?php
						if( ($this->session->userdata('main_group_akses110') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="input-data-mustahiq">
						  <a href="#"><i class="fa fa-folder"></i> Mustahiq/Penyaluran <i class="fa fa-angle-left pull-right"></i></a>
						  <ul class="treeview-menu">
						  
								<?php
								if( ($this->session->userdata('sub_group_akses1101') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-mustahiq-kategori"><a href="<?=base_url()?>admin-jabatan"><i class="fa fa-edit"></i> Kat.Mustahiq/Penyaluran </a></li>
								<?php
								}
								?>
							
								<?php
								if( ($this->session->userdata('sub_group_akses1102') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-mustahiq-muzaki"><a href="<?=base_url()?>admin-karyawan"><i class="fa fa-edit"></i> Mustahiq/Penyaluran </a></li>
								<?php
								}
								?>
						  </ul>
						</li>
						<?php
						}
						?>
					
					
						<?php
						if( ($this->session->userdata('main_group_akses111') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="input-data-partener">
						  <a href="#"><i class="fa fa-folder"></i> Partner Baznas <i class="fa fa-angle-left pull-right"></i></a>
						  <ul class="treeview-menu">
						  
								<?php
								if( ($this->session->userdata('sub_group_akses1111') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-partener-kategori"><a href="<?=base_url()?>admin-jabatan"><i class="fa fa-edit"></i> Kat.Partner </a></li>
								<?php
								}
								?>
							
								<?php
								if( ($this->session->userdata('sub_group_akses1112') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-partener-partener"><a href="<?=base_url()?>admin-karyawan"><i class="fa fa-edit"></i> Partener </a></li>
								<?php
								}
								?>
						  </ul>
						</li>
						<?php
						}
						?>
					
						<?php
						if( ($this->session->userdata('main_group_akses112') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="input-data-partener">
						  <a href="#"><i class="fa fa-folder"></i> Ilmu dan Tausiyah <i class="fa fa-angle-left pull-right"></i></a>
						  <ul class="treeview-menu">
							
								<?php
								if( ($this->session->userdata('sub_group_akses1121') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-partener-kategori"><a href="<?=base_url()?>admin-jabatan"><i class="fa fa-edit"></i> Kat.Ilmu/Tausiyah </a></li>
								<?php
								}
								?>
							
								<?php
								if( ($this->session->userdata('sub_group_akses1122') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-partener-partener"><a href="<?=base_url()?>admin-karyawan"><i class="fa fa-edit"></i> Ilmu/Tausiyah </a></li>
								<?php
								}
								?>
						  </ul>
						</li>
						<?php
						}
						?>
					
						<?php
						if( ($this->session->userdata('main_group_akses113') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="input-data-amal">
						  <a href="#"><i class="fa fa-folder"></i> Acara Amal <i class="fa fa-angle-left pull-right"></i></a>
						  <ul class="treeview-menu">
						  
								<?php
								if( ($this->session->userdata('sub_group_akses1131') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-amal-kategori"><a href="<?=base_url()?>admin-kategori-amal"><i class="fa fa-edit"></i> Kat.Acara Amal </a></li>
								<?php
								}
								?>
							
								<?php
								if( ($this->session->userdata('sub_group_akses1132') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-amal-amal"><a href="<?=base_url()?>admin-amal"><i class="fa fa-edit"></i> Acara Amal </a></li>
								<?php
								}
								?>
						  </ul>
						</li>
						<?php
						}
						?>
					
						<?php
						if( ($this->session->userdata('main_group_akses114') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="input-data-partener">
						  <a href="#"><i class="fa fa-folder"></i> Promo/Reward <i class="fa fa-angle-left pull-right"></i></a>
						  <ul class="treeview-menu">
						  
								<?php
								if( ($this->session->userdata('sub_group_akses1141') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-partener-kategori"><a href="<?=base_url()?>admin-jabatan"><i class="fa fa-edit"></i> Kat.Promo/Reward </a></li>
								<?php
								}
								?>
							
								<?php
								if( ($this->session->userdata('sub_group_akses1142') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="input-data-partener-partener"><a href="<?=base_url()?>admin-karyawan"><i class="fa fa-edit"></i> Promo/Reward </a></li>
								<?php
								}
								?>
						  </ul>
						</li>
						<?php
						}
						?>
						
						<?php
						if( ($this->session->userdata('main_group_akses115') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
							<li id="input-tematik"><a href="<?=base_url()?>admin-tematik"><i class="fa fa-circle-o"></i>Tematik Brosur</a></li>
						<?php
						}
						?>
						
						<?php
						if( ($this->session->userdata('main_group_akses116') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
							<li id="input-bank"><a href="<?=base_url()?>admin-bank"><i class="fa fa-circle-o"></i>Bank Terdaftar</a></li>
						<?php
						}
						?>
						
						<?php
						if( ($this->session->userdata('main_group_akses117') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
							<li id="input-jadwal-sholat"><a href="<?=base_url()?>admin-jadwal-sholat"><i class="fa fa-circle-o"></i>Jadwal Sholat</a></li>
						<?php
						}
						?>
					
				  </ul>
				</li>
				<?php
				}
				?>
			<!-- CEK AKSES GROUP1 = 1 -->
			
			
			
			<!-- CEK AKSES TRANSAKSI -->
				<?php
				if( ($this->session->userdata('group1_akses2') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
				{
				?>
				<li class="treeview" id="transaksi">
				  <a href="#">
					<i class="fa fa-table"></i> <span>Transaksi</span>
					<i class="fa fa-angle-left pull-right"></i>
				  </a>
				  <ul class="treeview-menu">
					
					
						<?php
						if( ($this->session->userdata('main_group_akses21') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="transaksi-pemasukan-pos"><a href="<?=base_url()?>admin-transaksi-masuk-pos"><i class="fa fa-circle-o"></i> Masuk Dari Pos </a></li>
						<?php
						}
						?>
					
					
						<?php
						if( ($this->session->userdata('main_group_akses22') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="transaksi-pemasukan-muzaki"><a href="<?=base_url()?>admin-transaksi-masuk-muzaqi"><i class="fa fa-circle-o"></i> Masuk Dari Muzaki/Munfiq </a></li>
						<?php
						}
						?>
						
						<?php
						if( ($this->session->userdata('main_group_akses26') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="transaksi-pemasukan-lain"><a href="<?=base_url()?>admin-transaksi-masuk-lain"><i class="fa fa-circle-o"></i> Masuk Dari Lain </a></li>
						<?php
						}
						?>
					
						<?php
						if( ($this->session->userdata('main_group_akses23') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="transaksi-pengeluaran-pos"><a href="<?=base_url()?>admin-pengajuan-dokumen"><i class="fa fa-circle-o"></i> Keluar Ke Mustahiq </a></li>
						<?php
						}
						?>
					
						<?php
						if( ($this->session->userdata('main_group_akses24') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="transaksi-pengeluaran-pos"><a href="<?=base_url()?>admin-pengajuan-dokumen"><i class="fa fa-circle-o"></i> Keluar Ke Event </a></li>
						<?php
						}
						?>
					
						<?php
						if( ($this->session->userdata('main_group_akses25') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="transaksi-pengeluaran-operasional"><a href="<?=base_url()?>admin-transaksi-keluar-operasional"><i class="fa fa-circle-o"></i> Keluar Ke Operasional						
						</a></li>
						<?php
						}
						?>
					
					
				  </ul>
				</li>
				<?php
				}
				?>
			<!-- CEK AKSES TRANSAKSI -->
			
			<!-- CEK AKSES HASIL LAPORAN-->
				<?php
				if( ($this->session->userdata('group1_akses3') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
				{
				?>
					<li class="treeview" id="laporan">
					  <a href="#">
						<i class="fa fa-files-o"></i> <span>Laporan</span>
						<i class="fa fa-angle-left pull-right"></i>
					  </a>
					  <ul class="treeview-menu">
						
							<?php
							if( ($this->session->userdata('main_group_akses31') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
							{
							?>
							<li id="laporan-pemasukan">
							  <a href="#"><i class="fa fa-folder"></i> Pemasukan <i class="fa fa-angle-left pull-right"></i></a>
							  
							  <ul class="treeview-menu">
									<?php
									if( ($this->session->userdata('sub_group_akses311') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<li id="laporan-pemasukan-periode"><a href="<?=base_url()?>admin-laporan-pemasukan-periode"><i class="fa fa-edit"></i> Per periode </a></li>
									<?php
									}
									?>
								
									<?php
									if( ($this->session->userdata('sub_group_akses312') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<li id="input-data-karyawan-karyawan"><a href="<?=base_url()?>admin-karyawan"><i class="fa fa-edit"></i> Per Daerah </a></li>
									<?php
									}
									?>
								
								
									<?php
									if( ($this->session->userdata('sub_group_akses313') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<li id="input-data-karyawan-karyawan"><a href="<?=base_url()?>admin-karyawan"><i class="fa fa-edit"></i> Per Kategori </a></li>
									<?php
									}
									?>
							  </ul>
							</li>
							<?php
							}
							?>
							
							<?php
							if( ($this->session->userdata('main_group_akses32') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
							{
							?>
							<li id="input-data-karyawan">
							  <a href="#"><i class="fa fa-folder"></i> Pengeluaran <i class="fa fa-angle-left pull-right"></i></a>
							  
							  <ul class="treeview-menu">
							  
									<?php
									if( ($this->session->userdata('sub_group_akses321') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<li id="input-data-karyawan-kategori"><a href="<?=base_url()?>admin-jabatan"><i class="fa fa-edit"></i> Per periode </a></li>
									<?php
									}
									?>
								
									<?php
									if( ($this->session->userdata('sub_group_akses322') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<li id="input-data-karyawan-karyawan"><a href="<?=base_url()?>admin-karyawan"><i class="fa fa-edit"></i> Per Daerah </a></li>
									<?php
									}
									?>
								
									<?php
									if( ($this->session->userdata('sub_group_akses323') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<li id="input-data-karyawan-karyawan"><a href="<?=base_url()?>admin-karyawan"><i class="fa fa-edit"></i> Per Kategori </a></li>
									<?php
									}
									?>
							  </ul>
							</li>
							<?php
							}
							?>
							
							
							<?php
							if( ($this->session->userdata('main_group_akses33') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
							{
							?>
							<li id="input-data-karyawan">
							  <a href="#"><i class="fa fa-folder"></i> Neraca <i class="fa fa-angle-left pull-right"></i></a>
							  
							  <ul class="treeview-menu">
							  
									<?php
									if( ($this->session->userdata('sub_group_akses331') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<li id="input-data-karyawan-kategori"><a href="<?=base_url()?>admin-jabatan"><i class="fa fa-edit"></i> Per periode </a></li>
									<?php
									}
									?>
								
									<?php
									if( ($this->session->userdata('sub_group_akses332') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<li id="input-data-karyawan-karyawan"><a href="<?=base_url()?>admin-karyawan"><i class="fa fa-edit"></i> Per Daerah </a></li>
									<?php
									}
									?>
								
								
									<?php
									if( ($this->session->userdata('sub_group_akses333') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<li id="input-data-karyawan-karyawan"><a href="<?=base_url()?>admin-karyawan"><i class="fa fa-edit"></i> Per Kategori </a></li>
									<?php
									}
									?>
							  </ul>
							</li>
							<?php
							}
							?>
						
					  </ul>
					</li>
				<?php
				}
				?>
					
			<!-- CEK AKSES HASIL LAPORAN-->
			
			<!-- CEK AKSES HASIL LAPORAN-->
				<?php
				if( ($this->session->userdata('group1_akses4') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
				{
				?>
					<li id="statistik-dokumen" class="treeview">
					  <a href="<?=base_url()?>admin-statistik-dokumen">
						<i class="fa fa-pie-chart"></i> <span>Statistik</span>
						<i class="fa fa-angle-left pull-right"></i>
					  </a>
					  
					  <ul class="treeview-menu">
						
							<?php
							if( ($this->session->userdata('main_group_akses41') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
							{
							?>
							<li id="input-data-karyawan">
							  <a href="#"><i class="fa fa-folder"></i> Pemasukan <i class="fa fa-angle-left pull-right"></i></a>
							  
							  <ul class="treeview-menu">
							  
									<?php
									if( ($this->session->userdata('sub_group_akses411') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<li id="input-data-karyawan-kategori"><a href="<?=base_url()?>admin-jabatan"><i class="fa fa-edit"></i> Per periode </a></li>
									<?php
									}
									?>
								
									<?php
									if( ($this->session->userdata('sub_group_akses412') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<li id="input-data-karyawan-karyawan"><a href="<?=base_url()?>admin-karyawan"><i class="fa fa-edit"></i> Per Daerah </a></li>
									<?php
									}
									?>
									
									<?php
									if( ($this->session->userdata('sub_group_akses413') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<li id="input-data-karyawan-karyawan"><a href="<?=base_url()?>admin-karyawan"><i class="fa fa-edit"></i> Per Kategori </a></li>
									<?php
									}
									?>
							  </ul>
							</li>
							<?php
							}
							?>
							
							<?php
							if( ($this->session->userdata('main_group_akses42') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
							{
							?>
							<li id="input-data-karyawan">
							  <a href="#"><i class="fa fa-folder"></i> Pengeluaran <i class="fa fa-angle-left pull-right"></i></a>
							  
							  <ul class="treeview-menu">
							  
									<?php
									if( ($this->session->userdata('sub_group_akses421') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<li id="input-data-karyawan-kategori"><a href="<?=base_url()?>admin-jabatan"><i class="fa fa-edit"></i> Per periode </a></li>
									<?php
									}
									?>
								
									<?php
									if( ($this->session->userdata('sub_group_akses422') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<li id="input-data-karyawan-karyawan"><a href="<?=base_url()?>admin-karyawan"><i class="fa fa-edit"></i> Per Daerah </a></li>
									<?php
									}
									?>
								
									<?php
									if( ($this->session->userdata('sub_group_akses423') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<li id="input-data-karyawan-karyawan"><a href="<?=base_url()?>admin-karyawan"><i class="fa fa-edit"></i> Per Kategori </a></li>
									<?php
									}
									?>
							  </ul>
							</li>
							<?php
							}
							?>
					  </ul>
					  
					</li>
				<?php
				}
				?>
			<!-- CEK AKSES HASIL LAPORAN-->
			
          </ul>
        </section>
        <!-- /.sidebar -->
      </aside>