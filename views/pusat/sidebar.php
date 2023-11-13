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
              <a href="<?php echo base_url()?>gl-pusat-dashboard"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a>
            </li>
			
			<li id="3_pengaturan_aplikasi" class="treeview">
				<a href="<?php echo base_url();?>gl-admin-pengaturan-aplikasi"><i class="fa fa-wrench"></i> <span>Pengaturan Aplikasi</span></a>
			</li>
			
			<li id="31_kantor" class="treeview">
				<a href="<?php echo base_url();?>gl-pusat-pengaturan-kantor"><i class="fa fa-bank"></i> <span>Kantor</span></a>
			</li>
					
			
			
			
			
			<!-- HRD -->
				
				<?php
				//if( ($this->session->userdata('ses_akses_lvl1_6') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
				//{
				?>
				<li class="treeview" id="2_HRD">
				  <a href="#">
					<i class="fa fa-users"></i> <span>HRD</span>
					<i class="fa fa-angle-left pull-right"></i>
				  </a>
				  <ul class="treeview-menu">
					
								<!--
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_211') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
								{
								?>
								<li id="211_basis_data_karyawan_jabatan"><a href="<?php echo base_url();?>gl-admin-jabatan"><i class="fa fa-edit"></i> Jabatan </a></li>
								<?php
								}
								?>
								
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_212') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
								{
								?>
								<li id="212_basis_data_karyawan_departement"><a href="<?php echo base_url();?>gl-admin-departement"><i class="fa fa-edit"></i> Departemen </a></li>
								<?php
								}
								?>
								
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_213') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
								{
								?>
								<li id="213_basis_data_karyawan_recruitment"><a href="<?php echo base_url();?>gl-admin-recruitment"><i class="fa fa-edit"></i> Recruitment </a></li>
								<?php
								}
								?>
								
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_214') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
								{
								?>
								<li id="214_basis_data_karyawan_training"><a href="<?php echo base_url()?>gl-admin-training"><i class="fa fa-edit"></i> Training </a></li>
								<?php
								}
								?>
								
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_215') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
								{
								?>
								<li id="215_basis_data_karyawan_promosi"><a href="<?php echo base_url()?>gl-admin-promosi"><i class="fa fa-edit"></i>Mutasi, Promosi & Demosi </a></li>
								<?php
								}
								?>
								-->
								
								<?php
								//if( ($this->session->userdata('ses_akses_lvl3_216') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
								//{
								?>
									<li id="216_basis_data_karyawan_karyawan"><a href="<?=base_url()?>gl-pusat-data-karyawan"><i class="fa fa-edit"></i> Data Karyawan </a></li>
									<li id="217_basis_data_rekap_absensi"><a href="<?=base_url()?>gl-pusat-rekap-absensi"><i class="fa fa-edit"></i> Rekap Absensi </a></li>
									<li id="2182_basis_data_karyawan_reward_peraturan"><a href="<?=base_url()?>gl-pusat-admin-peraturan"><i class="fa fa-edit"></i> Peraturan </a></li>
									<li id="2183_basis_data_karyawan_reward_punishment"><a href="<?=base_url()?>gl-pusat-admin-punishment"><i class="fa fa-edit"></i> Punishment </a></li>
								<?php
								//}
								?>
								
								<!--
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_217') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
								{
								?>
								<li id="217_basis_data_karyawan_sop"><a href="<?=base_url()?>gl-admin-sop"><i class="fa fa-edit"></i> SOP </a></li>
								<?php
								}
								?>
								
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_218') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
								{
								?>
								<li id="218_basis_data_karyawan_reward">
									<a href="#"><i class="fa fa-edit"></i>Reward & Punishment<i class="fa fa-angle-left pull-right"></i></a>
									<ul class="treeview-menu">
										<?php
										if( ($this->session->userdata('ses_akses_lvl4_2182') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
										{
										?>
											<li id="2182_basis_data_karyawan_reward_peraturan"><a href="<?=base_url()?>gl-admin-peraturan"><i class="fa fa-edit"></i> Peraturan </a></li>
										<?php
										}
										?>
										
										<?php
										if( ($this->session->userdata('ses_akses_lvl4_2183') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
										{
										?>
											<li id="2183_basis_data_karyawan_reward_punishment"><a href="<?=base_url()?>gl-admin-punishment"><i class="fa fa-edit"></i> Punishment </a></li>
										<?php
										}
										?>
									</ul>
								</li>
								<?php
								}
								?>
							
								<?php
								if( ($this->session->userdata('ses_akses_lvl3_219') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
								{
								?>
								<li id="219_basis_data_karyawan_akun">
									<a href="<?=base_url()?>gl-admin-akun"><i class="fa fa-edit"></i>Pemberian Akun</a>
								</li>
								<?php
								}
								?>
							
					
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_62') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
						{
						?>
						<li id="62_penggajian_proses"><a href="<?=base_url()?>admin-transaksi-masuk-pos"><i class="fa fa-money"></i> Proses Penggajian </a></li>
						<?php
						}
						?>
						-->
					
				  </ul>
				</li>
				<?php
				//}
				?>
				
			

			<li id="23_basis_data_pasien">
				<?php
				if($this->session->userdata('ses_gnl_isToko') == 'Y') //MEMASTIKAN IS TOKO
				{
				?>
					<a href="#"><i class="fa fa-folder"></i> Toko <i class="fa fa-angle-left pull-right"></i></a>
				<?php
				}
				else	
				{
				?>
					<a href="#"><i class="fa fa-folder"></i> Klinik <i class="fa fa-angle-left pull-right"></i></a>
				<?php
				}
				?>
				  <ul class="treeview-menu">
				  
					
					
					<?php
					if($this->session->userdata('ses_gnl_isToko') == 'Y') //MEMASTIKAN IS TOKO
					{
					?>
						<li id="31_pasien" class="treeview"><a href="<?php echo base_url();?>gl-pusat-pasien"><i class="fa fa-heartbeat"></i> <span>Data Pelanggan</span></a></li>
					<?php
					}
					else	
					{
					?>
						<li id="31_pasien" class="treeview"><a href="<?php echo base_url();?>gl-pusat-pasien"><i class="fa fa-heartbeat"></i> <span>Data Pasien</span></a></li>
					<?php
					}
					?>
					
					<li id="52_akuntansi_pendapatan"><a href="<?=base_url()?>gl-pusat-laporan-transaksi-pendapatan"><i class="fa fa-cart-plus "></i> Laporan Pendapatan </a></li>
					<li id="51_akuntansi_transaksi"><a href="<?=base_url()?>gl-pusat-laporan-transaksi"><i class="fa fa-cart-plus "></i> Detail Transaksi </a></li>	
					
					
					<?php
					if($this->session->userdata('ses_gnl_isToko') == 'N') //MEMASTIKAN IS TOKO
					{
					?>
						<li id="53_galeri_foto"><a href="<?=base_url()?>gl-pusat-galeri-foto-pasien"><i class="fa fa-users "></i> Galeri Foto Tindakan </a></li>
					<?php
					}
					?>
					
						
				  </ul>
				</li>
			
			<!-- PRODUK -->
				
				<?php
				//if( ($this->session->userdata('ses_akses_lvl2_22') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
				//{
				?>
				
				<li class="treeview" id="22_basis_data_produk">
				  <a href="#">
					<i class="fa fa-folder"></i> <span>Produk  Jasa</span>
					<i class="fa fa-angle-left pull-right"></i>
				  </a>
				  <ul class="treeview-menu">
				  
						<?php
						//if( ($this->session->userdata('ses_akses_lvl3_221') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
						//{
						?>
						<li id="221_basis_data_produk_kategori"><a href="<?=base_url()?>gl-pusat-kategori-produk-jasa"><i class="fa fa-edit"></i> Kategori Produk & Jasa</a></li>
						<?php
						//}
						?>
						
						<?php
						//if( ($this->session->userdata('ses_akses_lvl3_222') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
						//{
						?>
						<li id="222_basis_data_produk_satuan"><a href="<?=base_url()?>gl-pusat-satuan-produk-jasa"><i class="fa fa-edit"></i> Satuan Transaksi </a></li>
						<?php
						//}
						?>
						
						<?php
						//if( ($this->session->userdata('ses_akses_lvl2_10') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
						//{
						?>
						<li id="210_media_transaksi"><a href="<?php echo base_url();?>gl-pusat-media-produk-jasa"><i class="fa fa-edit"></i> Media Transaksi </a></li>
						<?php
						//}
						?>
					
						<?php
						//if( ($this->session->userdata('ses_akses_lvl3_223') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
						//{
						?>
						<li id="223_basis_data_produk_produk"><a href="<?=base_url()?>gl-pusat-produk-jasa"><i class="fa fa-edit"></i> Data Produk & Jasa </a></li>
						<?php
						//}
						?>
						
						<?php
							//if( ($this->session->userdata('ses_akses_lvl1_4') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
							//{
							?>
							<li class="treeview" id="4_pengaturan_Operasional">
							  <a href="#">
								<i class="fa fa-gear"></i> <span>Pengaturan Operasional</span>
								<i class="fa fa-angle-left pull-right"></i>
							  </a>
							  <ul class="treeview-menu">
								
								
									<?php
									//if( ($this->session->userdata('ses_akses_lvl2_41') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
									//{
									?>
									<li id="41_pengaturan_Operasional_konvSatuan"><a href="<?php echo base_url();?>gl-pusat-satuan-konversi"><i class="fa fa-circle-o"></i> Konversi Satuan </a></li>
									<?php
									//}
									?>
								
								
									<?php
									//if( ($this->session->userdata('ses_akses_lvl2_42') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
									//{
									?>
									<li id="42_pengaturan_Operasional_hargaDasar"><a href="<?php echo base_url();?>gl-pusat-harga-dasar-satuan"><i class="fa fa-circle-o"></i> Harga Dasar </a></li>
									<?php
									//}
									?>
									
									<?php
									//if( ($this->session->userdata('ses_akses_lvl2_43') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
									//{
									?>
									<li id="43_pengaturan_Operasional_hargaPasien"><a href="<?php echo base_url();?>gl-pusat-harga-pasien"><i class="fa fa-circle-o"></i> Harga Untuk Pasien </a></li>
									<?php
									//}
									?>

									<?php
									if( ($this->session->userdata('ses_akses_lvl3_441') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
									{
									?>
									<!--
									<li id="441_pengaturan_Operasional_Diskon_diskon"><a href="<?=base_url()?>gl-admin-diskon-promo"><i class="fa fa-edit"></i> Diskon & Promo</a></li>
									-->
									<?php
									}
									?>
									
									
									<?php
									if( ($this->session->userdata('ses_akses_lvl2_45') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
									{
									?>
									<!--
									<li id="45_Upload_produk"><a href="<?php echo base_url();?>gl-admin-pengaturan-upload-excel"><i class="fa fa-circle-o"></i> Upload Excel </a></li>
									-->
									<?php
									}
									?>
								
							  </ul>
							</li>
							<?php
							//}
							?>
				  </ul>
				</li>
				<?php
				//}
				?>
				
			<!-- PRODUK -->
			
			<!-- INVENTORI -->
				<?php
				//if( ($this->session->userdata('ses_akses_lvl1_6') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
				//{
				?>
				<li class="treeview" id="4_inventori">
				  <a href="#">
					<i class="fa fa-university"></i> <span>Inventori</span>
					<i class="fa fa-angle-left pull-right"></i>
				  </a>
				  <ul class="treeview-menu">
						
						<li id="242_basis_data_supplier_supplier"><a href="<?=base_url()?>gl-pusat-admin-supplier"><i class="fa fa-edit"></i> Data Supplier </a></li>
						<li id="511_purchase_order"><a href="<?=base_url()?>gl-pusat-admin-laporan-pembelian"><i class="fa fa-edit"></i> Laporan Purchase Order</a></li>
						<li id="512_penerimaan_order"><a href="<?php echo base_url();?>gl-pusat-admin-supplier-penerimaan-po"><i class="fa fa-edit"></i> Penerimaan Produk </a></li>
						<li id="712_laporan_general_mutasi"><a href="<?=base_url()?>gl-pusat-admin-laporan-mutasi-produk"><i class="fa fa-edit"></i> Laporan Mutasi </a></li>
						
						<li id="711_laporan_general_request_cabang">
							<a href="<?=base_url()?>gl-pusat-admin-laporan-permintaan-cabang">
								<span><i class="fa fa-edit"></i> Pemesanan Cabang </span>
								<span class="pull-right-container">
								</span>
							</a>
						</li>


						<?php
						//if( ($this->session->userdata('ses_akses_lvl2_61') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
						//{
						?>
						<li id="41_inventori_flow"><a href="<?=base_url()?>gl-pusat-inventori-flow"><i class="fa fa-random "></i> Pergerakan Produk </a></li>
						<?php
						//}
						?>
					
						<?php
						//if( ($this->session->userdata('ses_akses_lvl2_62') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
						//{
						?>
						<li id="42_inventori_analisa"><a href="<?=base_url()?>gl-pusat-inventori-produk-terlaris"><i class="fa fa-search-plus"></i> Analisa Produk</a></li>
						<?php
						//}
						?>
						
						<?php
						//if( ($this->session->userdata('ses_akses_lvl2_62') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
						//{
						?>
						
						<li id="43_inventori_stock"><a href="<?=base_url()?>gl-pusat-inventori-produk-stock?kode_kantor=PST"><i class="fa fa-institution "></i> Stock Produk</a></li>
						
						<li id="43_inventori_stock_tgl_exp"><a href="<?php echo base_url();?>gl-pusat-inventori-produk-stock-expired"><i class="fa fa-calendar"></i> Expired Produk </a></li>
						
						<?php
						//}
						?>
					
				  </ul>
				</li>
				<?php
				//}
				?>
			<!-- INVENTORI -->
			
			<!-- CEK AKSES LEVEL 1 MAIN 6 PENGGAJIAN -->
				<?php
				//if( ($this->session->userdata('ses_akses_lvl1_6') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
				//{
				?>
				<li class="treeview" id="6_penggajian">
				  <a href="#">
					<i class="fa fa-money"></i> <span>Penggajian</span>
					<i class="fa fa-angle-left pull-right"></i>
				  </a>
				  <ul class="treeview-menu">


						<!-- TAMBAH BY IRMAN -->
						<li id="63_penggajian_gapok"><a href="<?=base_url()?>gl-admin-gapok"><i class="fa fa-money"></i> Gaji Pokok </a></li>
						<li id="63_penggajian_upselling"><a href="<?=base_url()?>gl-admin-upselling"><i class="fa fa-money"></i> Up Selling </a></li>
						
						<li id="63_penggajian_jam_kerja"><a href="<?=base_url()?>gl-admin-jamkerja"><i class="fa fa-money"></i> Jam Kerja </a></li>
						<li id="63_penggajian_jam_kerja_karyawan"><a href="<?=base_url()?>gl-admin-jamkerja-karyawan"><i class="fa fa-money"></i> Jam Kerja Karyawan</a></li>
						<li id="63_penggajian_tunjangan"><a href="<?=base_url()?>gl-admin-tunjangan"><i class="fa fa-money"></i> Tunjangan </a></li>
						<li id="64_penggajian_tunjangan_karyawan"><a href="<?=base_url()?>gl-admin-tunjangan-karyawan"><i class="fa fa-money"></i> Tunjangan Karyawan</a></li>

						<li id="65_penggajian_potongan"><a href="<?=base_url()?>gl-admin-potongan"><i class="fa fa-money"></i> Potongan </a></li>
						<li id="66_penggajian_potongan_karyawan"><a href="<?=base_url()?>gl-admin-potongan-karyawan"><i class="fa fa-money"></i> Potongan Karyawan </a></li>

						<li id="66_penggajian_hitung_gaji"><a href="<?=base_url()?>gl-admin-hitung-gaji"><i class="fa fa-money"></i> Hitunga Gaji </a></li>

				  </ul>
				</li>
				<?php
				//}
				?>
			<!-- CEK AKSES LEVEL 1 MAIN 6 PENGGAJIAN -->
			
			
			<!-- AKUNTANSI -->
				<?php
				//if( ($this->session->userdata('ses_akses_lvl1_6') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
				//{
				?>
				<li class="treeview" id="5_akuntansi_real">
				  <a href="#">
					<i class="fa fa-money"></i> <span>Akuntansi</span>
					<i class="fa fa-angle-left pull-right"></i>
				  </a>
				  <ul class="treeview-menu">
					
						<?php
						//if( ($this->session->userdata('ses_akses_lvl2_61') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
						//{
						?>
						
						<!-- <li id="262_basis_data_assets_assets"><a href="<?php echo base_url();?>gl-admin-assets-pinjaman"><i class="fa fa-edit"></i> Data Assets/Pinjaman </a></li> -->

						<li id="252_basis_data_keuangan_kodeAkuntansi"><a href="<?=base_url()?>gl-pusat-kode-akuntansi"><i class="fa fa-edit"></i> Kode Akuntansi </a></li>
						
						<li id="59_transaksi_Pemasukan_pusat"><a href="<?=base_url()?>gl-pusat-admin-pemasukan"><i class="fa fa-sign-in"></i> Pemasukan
						</a></li>
						
						<li id="58_transaksi_pengeluaran_pusat"><a href="<?=base_url()?>gl-pusat-admin-pengeluaran"><i class="fa fa-sign-out"></i> Pengeluaran						
						</a></li>
						
						<li id="59_transaksi_mutasi_kas_pusat"><a href="<?=base_url()?>gl-pusat-admin-mutasi-kas"><i class="fa fa-exchange"></i> Mutasi Kas						
						</a></li>
						
						<li id="59_transaksi_jurnal_umum_pusat"><a href="<?=base_url()?>gl-pusat-admin-jurnal-umum-pembalik"><i class="fa fa-file-word-o"></i> Jurnal Umum						
						</a></li>
						
						<!-- "GAK DIPAKAI LAGI KARENA AMBIL LANGSUNG DARI PENJUALAN"
						<li id="510_transaksi_transfer_ke_akun"><a href="<?=base_url()?>gl-pusat-admin-transfer-ke-akun"><i class="fa fa-exchange"></i> Tarik Transaksi Ke Akun						
						</a></li>
						-->
						
						<li id="52_akuntansi_jurnal"><a href="<?=base_url()?>gl-pusat-akuntansi-jurnal"><i class="fa fa-cart-plus "></i> Jurnal </a></li>
						
						<li id="51_akuntansi_buku_besar"><a href="<?=base_url()?>gl-pusat-akuntansi-buku-besar"><i class="fa fa-cart-plus "></i> Buku Besar </a></li>
						
						<li id="53_akuntansi_laporan_keuangan"><a href="<?=base_url()?>gl-pusat-akuntansi-laporan-keuangan"><i class="fa fa-money "></i> Laporan Keuangan </a></li>
						
						<li id="53_akuntansi_laba_rugi"><a href="<?=base_url()?>gl-pusat-akuntansi-laporan-laba-rugi"><i class="fa fa-money "></i> Laba Rugi </a></li>
						
						<li id="53_akuntansi_neraca"><a href="<?=base_url()?>gl-pusat-akuntansi-laporan-neraca"><i class="fa fa-money "></i> Neraca </a></li>
						
						<?php
						//}
						?>
					
						<!--
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_62') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
						{
						?>
						<li id="52_akuntansi_jurnal_keuangan"><a href="<?=base_url()?>admin-transaksi-masuk-pos"><i class="fa fa-money"></i> Jurnal Keuangan </a></li>
						<?php
						}
						?>
						
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_62') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
						{
						?>
						<li id="53_akuntansi_buku_besar"><a href="<?=base_url()?>admin-transaksi-masuk-pos"><i class="fa fa-folder-open"></i> Buku Besar </a></li>
						<?php
						}
						?>
						
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_62') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
						{
						?>
						<li id="54_akuntansi_laba_rugi"><a href="<?=base_url()?>admin-transaksi-masuk-pos"><i class="fa fa-money"></i> Laba Rugi </a></li>
						<?php
						}
						?>
						
						<?php
						if( ($this->session->userdata('ses_akses_lvl2_62') > 0) or (strtoupper($this->session->userdata('ses_nama_jabatan')) == strtoupper('Admin Aplikasi')) )
						{
						?>
						<li id="55_akuntansi_neraca"><a href="<?=base_url()?>admin-transaksi-masuk-pos"><i class="fa fa-balance-scale"></i> Neraca </a></li>
						<?php
						}
						?>
						-->
					
				  </ul>
				</li>
				<?php
				//}
				?>
			<!-- AKUNTANSI -->
			
			<!-- AKTIFITAS -->
				<li class="treeview" id="6_aktifitas">
					<a href="#">
						<i class="fa fa-recycle"></i> <span>Aktifitas</span>
						<i class="fa fa-angle-left pull-right"></i>
					</a>
					<ul class="treeview-menu">
						<li id="61_aktifitas_log"><a href="<?=base_url()?>gl-pusat-aktifitas-log"><i class="fa fa-search "></i> Log Aktifitas </a></li>
						<li id="62_aktifitas_ubah"><a href="<?=base_url()?>gl-pusat-aktifitas-request-perubahan"><i class="fa fa-edit  "></i> Request Ubah Transaksi </a></li>
					</ul>
				</li>
			<!-- AKTIFITAS -->
			
			
          </ul>
        </section>
        <!-- /.sidebar -->
      </aside>