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
              <a href="<?=base_url()?>kecamatan-admin-dashboard"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a>
            </li>
			
			<!-- CEK AKSES GROUP1 = 1 -->
			
				<!--<li id="jenis-laporan" class="treeview">
				  <a href="#">
					<i class="fa fa-laptop"></i> <span>Jenis Laporan</span>
					<i class="fa fa-angle-left pull-right"></i>
				  </a>
				  <ul class="treeview-menu">-->
					
					<?php
					
						$list_klaporan = $this->M_akun->list_klaporan();
						if(!empty($list_klaporan))
						{  
							$list_result = $list_klaporan->result();
							$no = 1;
							foreach($list_result as $row)
							{
								//echo'<li id="klaporan-'.$row->KLAP_KODE.'"><a href="'.base_url().'kecamatan-list-laporan/'.$row->KLAP_KODE.'"><i class="fa fa-circle-o"></i> '.$row->KLAP_NAMA.'</a></li>';
								$no = $no + 1;
							}
						}
					
					?>
					
				  <!--</ul>
				</li>-->
			<!-- CEK AKSES GROUP1 = 1 -->
			
			<!-- CEK AKSES KECAMATAN -->
				<li id="input-data-kecamatan-kecamatan"><a href="<?=base_url()?>data-kecamatan"><i class="fa fa-edit"></i> Data Kecamatan </a></li>
			<!-- CEK AKSES KECAMATAN -->
			
			<!-- CEK AKSES KECAMATAN -->
				<!-- <li id="input-data-duk"><a href="<?=base_url()?>data-kecamatan"><i class="fa fa-edit"></i> Data Urutan Kepangkatan </a></li> -->
			<!-- CEK AKSES KECAMATAN -->
			
			<!-- CEK AKSES GROUP1 = 2 -->
			<li id="dashboard" class="treeview">
              <a href="<?=base_url()?>kecamatan-buat-laporan-dashboard"><i class="fa fa-edit"></i> <span>Buat Laporan</span></a>
            </li>
			<!-- CEK AKSES GROUP1 = 2 -->
			
			<!-- CEK AKSES GROUP1 = 2 -->
			<li id="dashboard" class="treeview">
              <a href="<?=base_url()?>detail-persetase-laporan"><i class="fa fa-table"></i> <span>
																								Status Laporan
																								<small class="label pull-right bg-red" id="hitung_catatan">0</small>
																							</span></a>
            </li>
			<!-- CEK AKSES GROUP1 = 2 -->
			
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