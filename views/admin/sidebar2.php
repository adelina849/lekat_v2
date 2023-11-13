
		
		<style><!-- 
        SPAN.searchword { background-color:yellow; }
        // -->
			.tooltip
			{
				/*Terserah desainnya bagaimana~*/
				background:#303030;
				padding:10px;
				color:#f0f0f0;
				border-radius:10px;
				-moz-border-radius:10px;
				width:200px;
				text-align:center;

				/*yang ini penting!*/
				position:absolute; 
			}
			
			.tooltip:before
			{
				/*wajib!*/
				content:"";
				position:absolute;
			 
				/*ini nih cara buat segitiga tanpa gambar dgn CSS*/
				border:10px solid transparent;
				border-bottom:10px solid #303030;
			 
				/*supaya lokasi segitiganya rapi*/
				top:-20px;
				left:10px;
			}
			
			.tooltip{display:none;} /*dalam keadaan biasa tidak tampil*/
			a.link:hover .tooltip{display:block;} /*ketika mouse diarahkan ke a.link, tooltip ditampilkan*/
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
              <p><?php echo $this->session->userdata('ses_nama_karyawan');?></p>
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
			<li id="1_dashboard" class="treeview">
              <a href="<?php echo base_url()?>gl-admin"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a>
            </li>
			
			<!-- CEK AKSES LEVEL 1 MAIN 2 DATA DASAR (BASIS DATA) -->
			
				<?php
				if( ($this->session->userdata('ses_akses_lvl1_2') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
				{
				?>
				<li id="2_basis_data" class="treeview">
				  <a href="#">
					<i class="fa fa-laptop"></i> 
						<span>Data Dasar (Basis Data)</span>
					<i class="fa fa-angle-left pull-right"></i>
				  </a>
				  <ul class="treeview-menu">
					
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_21') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="21_basis_data_karyawan">
						  <a href="#"><i class="fa fa-folder"></i> Data Karyawan <i class="fa fa-angle-left pull-right"></i></a>
						  
						  <ul class="treeview-menu">
						  
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_211') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="211_basis_data_karyawan_jabatan"><a href="<?php echo base_url();?>gl-admin-jabatan"><i class="fa fa-edit"></i> Jabatan </a></li>
								<?php
								}
								?>
								
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_212') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="212_basis_data_karyawan_departement"><a href="<?php echo base_url();?>gl-admin-departement"><i class="fa fa-edit"></i> Departemen </a></li>
								<?php
								}
								?>
								
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_213') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="213_basis_data_karyawan_recruitment"><a href="<?php echo base_url();?>gl-admin-recruitment"><i class="fa fa-edit"></i> Recruitment </a></li>
								<?php
								}
								?>
								
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_214') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="214_basis_data_karyawan_training"><a href="<?php echo base_url()?>gl-admin-training"><i class="fa fa-edit"></i> Training </a></li>
								<?php
								}
								?>
								
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_215') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="215_basis_data_karyawan_promosi"><a href="<?php echo base_url()?>gl-admin-promosi"><i class="fa fa-edit"></i>Mutasi, Promosi & Demosi </a></li>
								<?php
								}
								?>
							
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_216') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="216_basis_data_karyawan_karyawan"><a href="<?=base_url()?>gl-admin-data-karyawan"><i class="fa fa-edit"></i> Data Karyawan </a></li>
								<?php
								}
								?>
								
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_217') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="217_basis_data_karyawan_sop"><a href="<?=base_url()?>gl-admin-sop"><i class="fa fa-edit"></i> SOP </a></li>
								<?php
								}
								?>
								
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_218') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="218_basis_data_karyawan_reward">
									<a href="#"><i class="fa fa-edit"></i>Reward & Punishment<i class="fa fa-angle-left pull-right"></i></a>
									<ul class="treeview-menu">
										<?php
										if( ($this->session->userdata('ses_akses_lvl4_2182') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
										{
										?>
											<li id="2182_basis_data_karyawan_reward_peraturan"><a href="<?=base_url()?>gl-admin-peraturan"><i class="fa fa-edit"></i> Peraturan </a></li>
										<?php
										}
										?>
										
										<?php
										if( ($this->session->userdata('ses_akses_lvl4_2183') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
										{
										?>
											<li id="2183_basis_data_karyawan_reward_punishment"><a href="<?=base_url()?>gl-admin-punishment"><i class="fa fa-edit"></i> Punishment </a></li>
										<?php
										}
										?>
										
										<?php
										if( ($this->session->userdata('ses_akses_lvl4_2184') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
										{
										?>
											<!--<li id="2184_basis_data_karyawan_reward_reward"><a href="<?=base_url()?>admin-karyawan"><i class="fa fa-edit"></i> Reward </a></li>-->
										<?php
										}
										?>
										
									</ul>
								</li>
								<?php
								}
								?>
							
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_219') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="219_basis_data_karyawan_akun">
									<a href="<?=base_url()?>gl-admin-akun"><i class="fa fa-edit"></i>Pemberian Akun</a>
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
						if( ($this->session->userdata('ses_akses_lvl2_22') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="22_basis_data_produk">
						  <a href="#"><i class="fa fa-folder"></i> Produk & Jasa <i class="fa fa-angle-left pull-right"></i></a>
						  <ul class="treeview-menu">
						  
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_221') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="221_basis_data_produk_kategori"><a href="<?=base_url()?>gl-admin-kategori-produk-jasa"><i class="fa fa-edit"></i> Kategori Produk & Jasa</a></li>
								<?php
								}
								?>
								
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_222') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="222_basis_data_produk_satuan"><a href="<?=base_url()?>gl-admin-satuan-produk-jasa"><i class="fa fa-edit"></i> Satuan Transaksi </a></li>
								<?php
								}
								?>
							
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_223') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="223_basis_data_produk_produk"><a href="<?=base_url()?>gl-admin-produk-jasa"><i class="fa fa-edit"></i> Data Produk & Jasa </a></li>
								<?php
								}
								?>
								
						  </ul>
						</li>
						<?php
						}
						?>
					
						<?php
						if($this->session->userdata('ses_kode_kantor') == "SLM")
						{
						?>
							<li id="23_basis_data_pasien">
							  <a href="#"><i class="fa fa-folder"></i> Agent <i class="fa fa-angle-left pull-right"></i></a>
							  <ul class="treeview-menu">
							  
									<?php
									if( ($this->session->userdata('ses_akses_lvl3_231') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<li id="231_basis_data_pasien_kategori"><a href="<?php echo base_url();?>gl-admin-kategori-pasien"><i class="fa fa-edit"></i> Kategori Agent </a></li>
									<?php
									}
									?>
								
									<?php
									if( ($this->session->userdata('ses_akses_lvl3_232') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<li id="232_basis_data_pasien_pasien"><a href="<?=base_url()?>gl-admin-pasien"><i class="fa fa-edit"></i> Data Agent </a></li>
									
									<li id="232_basis_data_pasien_pasien_2"><a href="<?=base_url()?>gl-admin-pasien-service"><i class="fa fa-edit"></i> Customer Service </a></li>
									<?php
									}
									?>
									
							  </ul>
							</li>
						<?php
						}
						else
						{
						?>
						
							<?php
							if( ($this->session->userdata('ses_akses_lvl2_23') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
							{
							?>
							<li id="23_basis_data_pasien">
							  <a href="#"><i class="fa fa-folder"></i> Pasien <i class="fa fa-angle-left pull-right"></i></a>
							  <ul class="treeview-menu">
							  
									<?php
									if( ($this->session->userdata('ses_akses_lvl3_231') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<li id="231_basis_data_pasien_kategori"><a href="<?php echo base_url();?>gl-admin-kategori-pasien"><i class="fa fa-edit"></i> Kategori Pasien </a></li>
									<?php
									}
									?>
								
									<?php
									if( ($this->session->userdata('ses_akses_lvl3_232') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<li id="232_basis_data_pasien_pasien"><a href="<?=base_url()?>gl-admin-pasien"><i class="fa fa-edit"></i> Data Pasien </a></li>
									
									<li id="232_basis_data_pasien_pasien_2"><a href="<?=base_url()?>gl-admin-pasien-service"><i class="fa fa-edit"></i> Customer Service </a></li>
									<?php
									}
									?>
									
							  </ul>
							</li>
							<?php
							}
							?>
						
						<?php
						}
						?>
					
						
						
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_24') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="24_basis_data_supplier">
						  <a href="#"><i class="fa fa-folder"></i> Supplier <i class="fa fa-angle-left pull-right"></i></a>
						  <ul class="treeview-menu">
						  
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_241') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="241_basis_data_supplier_kategori"><a href="<?php echo base_url();?>gl-admin-kategori-supplier"><i class="fa fa-edit"></i> Kategori Supplier </a></li>
								<?php
								}
								?>
							
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_242') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="242_basis_data_supplier_supplier"><a href="<?=base_url()?>gl-admin-supplier"><i class="fa fa-edit"></i> Data Supplier </a></li>
								<?php
								}
								?>
								
						  </ul>
						</li>
						<?php
						}
						?>
					
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_25') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="25_basis_data_keuangan">
						  <a href="#"><i class="fa fa-folder"></i> Akun Keuangan <i class="fa fa-angle-left pull-right"></i></a>
						  <ul class="treeview-menu">
						  
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_251') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="251_basis_data_keuangan_bank"><a href="<?php echo base_url();?>gl-bank"><i class="fa fa-edit"></i> Data Bank </a></li>
								<?php
								}
								?>
							
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_252') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="252_basis_data_keuangan_kodeAkuntansi"><a href="<?=base_url()?>gl-kode-akuntansi"><i class="fa fa-edit"></i> Kode Akuntansi </a></li>
								<?php
								}
								?>
								
						  </ul>
						</li>
						<?php
						}
						?>
						
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_26') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="26_basis_data_assets">
						  <a href="#"><i class="fa fa-folder"></i> Assets/Pinjaman <i class="fa fa-angle-left pull-right"></i></a>
						  <ul class="treeview-menu">
						  
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_261') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="261_basis_data_assets_kategori"><a href="<?php echo base_url();?>gl-admin-kategori-assets"><i class="fa fa-edit"></i> Kategori Assets/Pinjaman </a></li>
								<?php
								}
								?>
							
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_262') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="262_basis_data_assets_assets"><a href="<?php echo base_url();?>gl-admin-assets-pinjaman"><i class="fa fa-edit"></i> Data Assets/Pinjaman </a></li>
								<?php
								}
								?>
								
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_263') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="263_basis_data_assets_ruangan"><a href="<?php echo base_url();?>gl-admin-ruangan-tempat-penyimpanan-barang"><i class="fa fa-edit"></i> Penyimpanan/Ruangan </a></li>
								<?php
								}
								?>
								
						  </ul>
						</li>
						<?php
						}
						?>
						
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_27') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="27_basis_data_penyakit"><a href="<?php echo base_url();?>gl-admin-penyakit"><i class="fa fa-edit"></i> Data Penyakit </a></li>
						<?php
						}
						?>
						
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_28') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<!--
						<li id="28_basis_data_kalender"><a href="<?=base_url()?>admin-halaman"><i class="fa fa-calendar-check-o "></i> Kalender Perusahaan </a></li>
						-->
						<?php
						}
						?>
					
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_29') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="29_penyedia_asuransi"><a href="<?php echo base_url();?>gl-admin-penyedia-asuransi"><i class="fa fa-edit"></i> Penyedia Asuransi </a></li>
						<?php
						}
						?>
						
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_10') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="210_media_transaksi"><a href="<?php echo base_url();?>gl-admin-media-transaksi"><i class="fa fa-edit"></i> Media Transaksi </a></li>
						<?php
						}
						?>
						
					
				  </ul>
				</li>
				<?php
				}
				?>
			<!-- CEK AKSES LEVEL 1 MAIN 2 DATA DASAR (BASIS DATA) -->
			
			<!-- CEK AKSES LEVEL 1 MAIN 3 PENGATURAN APLIKASI -->
				<?php
				if( ($this->session->userdata('ses_akses_lvl1_3') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
				{
				?>
					<li id="3_pengaturan_aplikasi" class="treeview">
						<a href="<?php echo base_url();?>gl-admin-pengaturan-aplikasi"><i class="fa fa-wrench"></i> <span>Pengaturan Aplikasi</span></a>
					</li>
				<?php
				}
				?>
			<!-- CEK AKSES LEVEL 1 MAIN 3 PENGATURAN APLIKASI -->
			
			<!-- CEK AKSES LEVEL 1 MAIN 4 PENGATURAN OPERASIONAL -->
				<?php
				if( ($this->session->userdata('ses_akses_lvl1_4') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
				{
				?>
				<li class="treeview" id="4_pengaturan_Operasional">
				  <a href="#">
					<i class="fa fa-gear"></i> <span>Pengaturan Operasional</span>
					<i class="fa fa-angle-left pull-right"></i>
				  </a>
				  <ul class="treeview-menu">
					
					
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_41') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="41_pengaturan_Operasional_konvSatuan"><a href="<?php echo base_url();?>gl-admin-satuan-konversi"><i class="fa fa-circle-o"></i> Konversi Satuan </a></li>
						<?php
						}
						?>
					
					
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_42') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="42_pengaturan_Operasional_hargaDasar"><a href="<?php echo base_url();?>gl-admin-harga-dasar-satuan"><i class="fa fa-circle-o"></i> Harga Dasar </a></li>
						<?php
						}
						?>
						
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_43') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="43_pengaturan_Operasional_hargaPasien"><a href="<?php echo base_url();?>gl-admin-harga-pasien"><i class="fa fa-circle-o"></i> Harga Untuk Pasien </a></li>
						<?php
						}
						?>

						
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_44') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="44_pengaturan_Operasional_Diskon">
						  <a href="#"><i class="fa fa-folder"></i> Diskon & Fee <i class="fa fa-angle-left pull-right"></i></a>
						  <ul class="treeview-menu">
						  
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_441') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="441_pengaturan_Operasional_Diskon_diskon"><a href="<?=base_url()?>gl-admin-diskon-promo"><i class="fa fa-edit"></i> Diskon & Promo</a></li>
								<?php
								}
								?>
								
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_442') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="442_pengaturan_Operasional_Diskon_paket"><a href="<?php echo base_url();?>gl-admin-pengaturan-fee"><i class="fa fa-edit"></i> Pengaturan Fee </a></li>
								<?php
								}
								?>
								
						  </ul>
						</li>
						<?php
						}
						?>
						
						
						
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_45') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="45_Upload_produk"><a href="<?php echo base_url();?>gl-admin-pengaturan-upload-excel"><i class="fa fa-circle-o"></i> Upload Excel </a></li>
						<?php
						}
						?>
					
				  </ul>
				</li>
				<?php
				}
				?>
			<!-- CEK AKSES LEVEL 1 MAIN 4 PENGATURAN OPERASIONAL -->
			
			
			
			<!-- CEK AKSES LEVEL 1 MAIN 5 TRANSAKSI -->
				<?php
				if( ($this->session->userdata('ses_akses_lvl1_5') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
				{
				?>
				<li class="treeview" id="5_transaksi">
					<a href="#">
						<i class="fa fa-table"></i> <span>Transaksi</span>
						<span class="pull-right-container">
							<i class="fa fa-angle-left pull-right"></i>
							<small id="sb_jum_tr" class="label pull-right bg-red">0</small>
						</span>
						
					</a>
				  <ul class="treeview-menu">
					
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_51') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<!--<li id="51_transaksi_pembelian"><a href="<?=base_url()?>gl-admin-purchase-order"><i class="fa fa-shopping-cart"></i> Pembelian Produk </a></li>-->
						<?php
						}
						?>
						
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_51') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="51_transaksi_pembelian">
						  <a href="#"><i class="fa fa-shopping-cart"></i> Pembelian/Purchase <i class="fa fa-angle-left pull-right"></i></a>
						  <ul class="treeview-menu">
						  
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_511') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								<li id="511_purchase_order"><a href="<?=base_url()?>gl-admin-purchase-order"><i class="fa fa-edit"></i> Purchase Order</a></li>
								<?php
								}
								?>
								
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_512') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
								?>
								
								<!--
								<li id="512_penerimaan_order"><a href="<?php echo base_url();?>gl-admin-purchase-order-terima"><i class="fa fa-edit"></i> Penerimaan Produk </a></li>
								-->
								
								<li id="512_penerimaan_order"><a href="<?php echo base_url();?>gl-admin-supplier-penerimaan-po"><i class="fa fa-edit"></i> Penerimaan Produk </a></li>
								
								<li id="513_penerimaan_order_tgl_exp"><a href="<?php echo base_url();?>gl-admin-purchase-order-terima-tgl-exp"><i class="fa fa-calendar"></i> Expired Produk </a></li>
								
								<?php
								}
								?>
								
						  </ul>
						</li>
						<?php
						}
						?>
						
						
						<?php
						if($this->session->userdata('ses_kode_kantor') == "SLM")
						{
						?>
							<li id="513_transaksi_pembayaran"><a href="<?=base_url()?>gl-admin-penjualan-langsung"><i class="fa fa-money"></i> Obat - Jasa Langsung </a></li>
						
							<li id="54_transaksi_pembayaran">
								<a href="<?=base_url()?>gl-admin-pembayaran">
									<i class="fa fa-shopping-cart"></i> 
									<span><font style="color:red;"></font>Pemesanan Agent </span>
									<span class="pull-right-container">
									  <small id="sb_pemeriksaan_dokter" class="label pull-right bg-red">0</small>
									</span>
								</a>
							</li>
							
							<li id="54_transaksi_pembayaran">
								<a href="<?=base_url()?>gl-admin-pembayaran">
									<i class="fa fa-money"></i> 
									<span><font style="color:red;"></font>Pemesanan Pembayaran </span>
									<span class="pull-right-container">
									  <small id="sb_pembayaran" class="label pull-right bg-red">0</small>
									</span>
								</a>
							</li>
							
							<li id="54_transaksi_pembayaran">
								<a href="<?=base_url()?>gl-admin-pembayaran">
									<i class="fa fa-truck"></i> 
									<span><font style="color:red;"></font>Pemesanan Selesai </span>
									<span class="pull-right-container">
									  <small id="sb_perawatan_lanjut" class="label pull-right bg-red">0</small>
									</span>
								</a>
							</li>
						<?php
						}
						else
						{
						?>
						
							<?php
							if( ($this->session->userdata('ses_akses_lvl2_513') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
							{
							?>
							<li id="513_transaksi_pembayaran"><a href="<?=base_url()?>gl-admin-penjualan-langsung"><i class="fa fa-money"></i> Obat - Jasa Langsung </a></li>
							<?php
							}
							?>
						
							<?php
							if( ($this->session->userdata('ses_akses_lvl2_52') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
							{
							?>
							<li id="52_transaksi_pendaftaran"><a href="<?=base_url()?>gl-admin-pendaftaran-pasien"><i class="fa fa-check-square-o"></i> <font style="color:red;">1.</font>Kunjungan Pasien</a></li>
							<?php
							}
							?>
						
						
							<?php
							if( ($this->session->userdata('ses_akses_lvl2_53') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
							{
							?>
							<li id="53_transaksi_pemeriksaan">
								<a href="<?=base_url()?>gl-admin-pemeriksaan-dokter">
									<i class="fa fa-stethoscope"></i> 
									<span><font style="color:red;">2.</font>Pemeriksaan Dokter</span>
									<span class="pull-right-container">
									  <small id="sb_pemeriksaan_dokter" class="label pull-right bg-red">0</small>
									</span>
								</a>
							</li>
							<?php
							}
							?>
							
							<?php
							if( ($this->session->userdata('ses_akses_lvl2_54') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
							{
							?>
							<li id="54_transaksi_pembayaran">
								<a href="<?=base_url()?>gl-admin-pembayaran">
									<i class="fa fa-money"></i> 
									<span><font style="color:red;">3.</font>Pembayaran </span>
									<span class="pull-right-container">
									  <small id="sb_pembayaran" class="label pull-right bg-red">0</small>
									</span>
								</a>
							</li>
							<?php
							}
							?>
							
							
							<?php
							if( ($this->session->userdata('ses_akses_lvl2_55') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
							{
							?>
							<li id="55_transaksi_perawatanLanjut">
								<a href="<?=base_url()?>gl-admin-perawatan-lanjut">
									<i class="fa fa-stethoscope"></i> 
									<span><font style="color:red;">4.</font>Perawatan Lanjutan</span>
									<span class="pull-right-container">
									  <small id="sb_perawatan_lanjut" class="label pull-right bg-red">0</small>
									</span>
								</a>
							</li>
							<?php
							}
							?>
						
							<?php
							if( ($this->session->userdata('ses_akses_lvl2_56') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
							{
							?>
							<li id="56_transaksi_statusTherapist">
								<a href="<?=base_url()?>gl-admin-status-therapist">
									<i class="fa fa-user-md"></i> 
									<span><font style="color:red;">5.</font>Status Therapist</span>
									<span class="pull-right-container">
									  <small id="sb_status_therapist" class="label pull-right bg-red">0</small>
									</span>
								</a>
							</li>
							<?php
							}
							?>
						
							<?php
							if( ($this->session->userdata('ses_akses_lvl2_57') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
							{
							?>
							<li id="57_transaksi_statusDokter"><a href="<?=base_url()?>gl-admin-status-dokter"><i class="fa fa-user-md"></i> <font style="color:red;">5.</font>Status Dokter </a></li>
							<?php
							}
							?>
							
						<?php
						}
						?>
						
						<?php
							if( ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') or ($this->session->userdata('ses_nama_jabatan') == 'SUPERVISOR') or ($this->session->userdata('ses_nama_jabatan') == 'ADMIN PUSAT') )
							{
							?>
							<li id="54_transaksi_pembayaran">
								<a href="<?=base_url()?>gl-admin-jamkerja-karyawan">
									<i class="fa fa-money"></i> 
									<span><font style="color:red;"></font>Jam Kerja Karyawan </span>
								</a>
							</li>
							<?php
							}
							?>
						
						<?php
							if( ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') or ($this->session->userdata('ses_nama_jabatan') == 'SUPERVISOR') or ($this->session->userdata('ses_nama_jabatan') == 'ADMIN PUSAT') )
							{
						?>
						<li id="54_transaksi_jam_kerja_karyawan">
							<a href="<?=base_url()?>gl-admin-upselling">
								<i class="fa fa-money"></i>
								<span><font style="color:red;"></font>Upselling </span>
							</a>
						</li>
						<?php
							}
						?>
						
					
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_58') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						
						<!--
						<li id="58_transaksi_pengeluaran"><a href="<?=base_url()?>gl-admin-pengeluaran"><i class="fa fa-sign-out"></i> Pengeluaran						
						</a></li>
						-->
						
						<?php
						}
						?>
						
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_59') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						
						<li id="59_transaksi_Pemasukan"><a href="<?=base_url()?>gl-admin-pemasukan"><i class="fa fa-sign-in"></i> Pemasukan						
						</a></li>
						
						<li id="58_transaksi_pengeluaran"><a href="<?=base_url()?>gl-admin-pengeluaran"><i class="fa fa-sign-out"></i> Pengeluaran						
						</a></li>
						
						<li id="59_transaksi_mutasi_kas"><a href="<?=base_url()?>gl-admin-mutasi-kas"><i class="fa fa-exchange"></i> Mutasi Kas						
						</a></li>
						
						
						<?php
						}
						?>
						
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_514') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="514_transaksi_retur_ke_supplier"><a href="<?=base_url()?>gl-admin-retur-supplier"><i class="fa fa-truck"></i> Retur Ke Supplier </a></li>
						<?php
						}
						?>
						
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_510') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="510_transaksi_Pemakaian"><a href="<?=base_url()?>gl-admin-mutasi-out"><i class="fa fa-minus-square"></i> Pemakainan Produk						
						</a></li>
						<?php
						}
						?>
						
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_511') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="511_transaksi_Penambahan"><a href="<?=base_url()?>gl-admin-mutasi-in"><i class="fa fa-plus-square"></i> Penambahan Produk						
						</a></li>
						<?php
						}
						?>
					
					
				  </ul>
				</li>
				<?php
				}
				?>
			<!-- CEK AKSES LEVEL 1 MAIN 5 TRANSAKSI -->
			
			
			<!-- CEK AKSES LEVEL 1 MAIN 6 PENGGAJIAN -->
				<?php
				if( ($this->session->userdata('ses_akses_lvl1_6') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
				{
				?>
				
				<!--
				<li class="treeview" id="6_penggajian">
				  <a href="#">
					<i class="fa fa-money"></i> <span>Penggajian</span>
					<i class="fa fa-angle-left pull-right"></i>
				  </a>
				  <ul class="treeview-menu">
					
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_61') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="61_penggajian_pengaturan"><a href="<?=base_url()?>admin-transaksi-masuk-pos"><i class="fa fa-gears "></i> Pengaturan Gaji </a></li>
						<?php
						}
						?>
					
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_62') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
						?>
						<li id="62_penggajian_proses"><a href="<?=base_url()?>admin-transaksi-masuk-pos"><i class="fa fa-money"></i> Proses Penggajian </a></li>
						<?php
						}
						?>
						
					
				  </ul>
				</li>
				-->
				<?php
				}
				?>
			<!-- CEK AKSES LEVEL 1 MAIN 6 PENGGAJIAN -->
			
			<!-- CEK AKSES LEVEL 1 MAIN 7 LAPORAN -->
				<?php
				if( ($this->session->userdata('ses_akses_lvl1_7') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
				{
				?>
					<li class="treeview" id="7_laporan">
					  <a href="#">
						<i class="fa fa-files-o"></i> <span>Laporan</span>
						<i class="fa fa-angle-left pull-right"></i>
					  </a>
					  <ul class="treeview-menu">
						
							<?php
							if( ($this->session->userdata('ses_akses_lvl2_71') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
							{
							?>
							<li id="71_laporan_general">
							  <a href="#"><i class="fa fa-folder"></i> General <i class="fa fa-angle-left pull-right"></i></a>
							  
							  <ul class="treeview-menu">
									<?php
									if( ($this->session->userdata('ses_akses_lvl3_711') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<li id="711_laporan_general_transaksi"><a href="<?=base_url()?>gl-admin-laporan-transaksi"><i class="fa fa-edit"></i> Transaksi </a></li>
									<?php
									}
									?>
									
									<?php
									
									if( ($this->session->userdata('ses_akses_lvl3_711') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
										if(
											($page_content <> 'gl_admin_h_penjualan_proses_dokter_proses')
											&& ($page_content <> 'gl_admin_stock_produk')
											&& ($page_content <> 'gl_admin_lap_keuangan')
											&& ($page_content <> 'gl_admin_satuan_konversi')
											&& ($page_content <> 'gl_admin_harga_dasar_satuan')
											&& ($page_content <> 'gl_admin_harga_pasien')
											&& ($page_content <> 'gl_admin_lap_jurnal')
											&& ($page_content <> 'gl_admin_analisa_order')
											&& ($page_content <> 'gl_admin_lap_hutang_supplier_tempo_vipot')
											&& ($page_content <> 'gl_admin_analisa_order_v2') 
										)
										{
											$get_request_cabang = $this->M_gl_pengaturan->get_request_cabang($this->session->userdata('ses_kode_kantor'));
											if(!empty($get_request_cabang))
											{
												$get_request_cabang = $get_request_cabang->row();
												$get_request_cabang = $get_request_cabang->CNT;
											}
											else
											{
												$get_request_cabang = 0;
											}
										}
										else
										{
											$get_request_cabang = 0;
										}
										
										//if($get_request_cabang > 0)
										//{
											?>
													<!--
													<audio id="soundPesanan" preload="auto">
														<source src="<?php echo base_url();?>assets/global/insight.mp3"></source>
														<source src="<?php echo base_url();?>assets/global/insight.ogg"></source>
													</audio>
													-->
												<?php
										//}
									?>
									
									<!--<li id="711_laporan_general_request_cabang"><a href="<?=base_url()?>gl-admin-laporan-permintaan-cabang"><i class="fa fa-edit"></i> Pemesanan Cabang </a></li>-->
									
									
		
									<li id="711_laporan_general_request_cabang">
										<a href="<?=base_url()?>gl-admin-laporan-permintaan-cabang">
											<span><i class="fa fa-edit"></i> Pemesanan Cabang </span>
											<span class="pull-right-container">
											  <small class="label pull-right bg-red"><?php echo $get_request_cabang;?></small>
											</span>
										</a>
									</li>
									
									<?php
									}
									?>
									
									<?php
									if( ($this->session->userdata('ses_akses_lvl3_712') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									
									<li id="712_laporan_general_mutasi"><a href="<?=base_url()?>gl-admin-laporan-mutasi-produk"><i class="fa fa-edit"></i> Mutasi </a></li>
									
									<?php
									}
									?>
									
									<?php
									if( ($this->session->userdata('ses_akses_lvl3_7110') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									
						
									<li id="7110_laporan_general_apoteker">
										<a href="<?=base_url()?>gl-admin-laporan-apoteker">
											<span><i class="fa fa-edit"></i> Apoteker </span>
											<span class="pull-right-container">
											  <small id="sb_apoteker" class="label pull-right bg-red">0</small>
											</span>
										</a>
									</li>
									<?php
									}
									?>
									
									<?php
									if( ($this->session->userdata('ses_akses_lvl3_7111') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<li id="7110_laporan_general_apoteker_front"><a href="<?=base_url()?>gl-admin-laporan-apoteker-front-desk"><i class="fa fa-edit"></i> Apoteker Front Desk </a></li>
									<?php
									}
									?>
									
									<?php
									if( ($this->session->userdata('ses_akses_lvl3_718') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<li id="718_laporan_general_purchase"><a href="<?=base_url()?>gl-admin-laporan-pembelian"><i class="fa fa-edit"></i> Purchase/Pembelian </a></li>
									<?php
									}
									?>
								
									
								
								
									<?php
									if( ($this->session->userdata('ses_akses_lvl3_713') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<!--
									<li id="713_laporan_general_dokter"><a href="<?=base_url()?>admin-karyawan"><i class="fa fa-edit"></i> Dokter </a></li>
									-->
									<?php
									}
									?>
									
									<?php
									if( ($this->session->userdata('ses_akses_lvl3_714') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<!--
									<li id="714_laporan_general_karyawan"><a href="<?=base_url()?>admin-karyawan"><i class="fa fa-edit"></i> Karyawan </a></li>
									-->
									<?php
									}
									?>
									
									<?php
									if( ($this->session->userdata('ses_akses_lvl3_715') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<li id="715_laporan_general_stok"><a href="<?=base_url()?>gl-admin-laporan-stock-produk"><i class="fa fa-edit"></i> Persediaan/Stok </a></li>
									<?php
									}
									?>
									
									<?php
									if( ($this->session->userdata('ses_akses_lvl3_716') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<li id="716_laporan_general_keuangan"><a href="<?=base_url()?>gl-admin-laporan-keuangan"><i class="fa fa-edit"></i> Keuangan </a></li>
									<?php
									}
									?>
									
									<?php
									if( ($this->session->userdata('ses_akses_lvl3_717') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<!--
									<li id="717_laporan_general_assets"><a href="<?=base_url()?>admin-karyawan"><i class="fa fa-edit"></i> Assets </a></li>
									-->
									<?php
									}
									?>
									
									<?php
									if( ($this->session->userdata('ses_akses_lvl3_719') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<li id="719_laporan_log_aktifitas"><a href="<?=base_url()?>gl-admin-laporan-log-aktifitas"><i class="fa fa-edit"></i> Log Aktifitas </a></li>
									<?php
									}
									?>
									
							  </ul>
							</li>
							<?php
							}
							?>
							
							<?php
							if( ($this->session->userdata('ses_akses_lvl2_72') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
							{
							?>
							<li id="72_laporan_akuntansi">
							  <a href="#"><i class="fa fa-folder"></i> Akuntansi <i class="fa fa-angle-left pull-right"></i></a>
							  
							  <ul class="treeview-menu">
							  
									<?php
									if( ($this->session->userdata('ses_akses_lvl3_721') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									
									<li id="721_laporan_akuntansi_jurnal"><a href="<?=base_url()?>gl-admin-laporan-jurnal"><i class="fa fa-edit"></i> Jurnal </a></li>
									
									<?php
									}
									?>
								
									<?php
									if( ($this->session->userdata('ses_akses_lvl3_722') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									<li id="722_laporan_akuntansi_bukuBesar"><a href="<?=base_url()?>gl-admin-laporan-buku-besar"><i class="fa fa-edit"></i> Buku Besar </a></li>
									
									<li id="722_laporan_akuntansi_laporanKeuangan"><a href="<?=base_url()?>gl-admin-laporan-acc-keuangan"><i class="fa fa-edit"></i> Laporan keuangan </a></li>
									
									<?php
									}
									?>
								
									<?php
									if( ($this->session->userdata('ses_akses_lvl3_723') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
									?>
									
									<!--
									<li id="723_laporan_akuntansi_neraca"><a href="<?=base_url()?>gl-admin-laporan-neraca"><i class="fa fa-edit"></i> Neraca Saldo </a></li>
									-->
									
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
					
			<!-- CEK AKSES LEVEL 1 MAIN 7 LAPORAN -->
			
			<!-- CEK AKSES LEVEL 1 MAIN 8 STATISTIK -->
				
				<?php
				if( ($this->session->userdata('ses_akses_lvl1_8') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
				{
				?>
					<!--
					<li id="8_statistik" class="treeview">
					  <a href="<?=base_url()?>admin-statistik-dokumen">
						<i class="fa fa-pie-chart"></i> <span>Statistik</span>
						<i class="fa fa-angle-left pull-right"></i>
					  </a>
					  
					  <ul class="treeview-menu">
					-->
							<?php
							if( ($this->session->userdata('ses_akses_lvl2_81') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
							{
							?>
							
							<!--
							<li id="81_statistik_transaksi"><a href="<?=base_url()?>admin-jabatan"><i class="fa fa-random"></i> Transaksi </a></li>
							-->
							
							<?php
							}
							?>
						
							<?php
							if( ($this->session->userdata('ses_akses_lvl2_82') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
							{
							?>
							
							<!--
							<li id="82_statistik_pasien"><a href="<?=base_url()?>admin-karyawan"><i class="fa fa-user"></i> Pasien </a></li>
							-->
							
							<?php
							}
							?>
							
							<?php
							if( ($this->session->userdata('ses_akses_lvl2_83') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
							{
							?>
							
							<!--
							<li id="83_statistik_produk"><a href="<?=base_url()?>admin-karyawan"><i class="fa fa-cart-plus"></i> Produk </a></li>
							-->
							
							<?php
							}
							?>
							
							<?php
							if( ($this->session->userdata('ses_akses_lvl2_84') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
							{
							?>
							
							<!--
							<li id="84_statistik_dokter"><a href="<?=base_url()?>admin-karyawan"><i class="fa fa-user-md"></i> Dokter </a></li>
							-->
							
							<?php
							}
							?>
							
							<?php
							if( ($this->session->userdata('ses_akses_lvl2_85') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
							{
							?>
							
							<!--
							<li id="85_statistik_therapist"><a href="<?=base_url()?>admin-karyawan"><i class="fa fa-user-md"></i> Therapist </a></li>
							-->
							
							<?php
							}
							?>
					<!-- 
					  </ul>
					  
					</li>
					-->
				<?php
				}
				?>
			<!-- CEK AKSES LEVEL 1 MAIN 8 STATISTIK -->
				
          </ul>
        </section>
        <!-- /.sidebar -->
      </aside>