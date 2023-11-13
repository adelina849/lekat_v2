<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_admin_periode extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_periode'));
		
	}
	
	public function index()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_akun->get_cek_login($this->session->userdata('ses_user_admin'),md5(base64_decode($this->session->userdata('ses_pass_admin'))));
			
			if(!empty($cek_ses_login))
			{
				// $data = array('page_content'=>'king_jabatan');
				// $this->load->view('admin/container',$data);
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "WHERE (PER_KODE LIKE '%".str_replace("'","",$_GET['cari'])."%' OR PER_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%')";
				}
				else
				{
					$cari = "";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				$config['first_url'] = site_url('periode-laporan?'.http_build_query($_GET));
				$config['base_url'] = site_url('periode-laporan/');
				$config['total_rows'] = $this->M_periode->count_periode_limit($cari)->JUMLAH;
				$config['uri_segment'] = 2;	
				$config['per_page'] = 30;
				$config['num_links'] = 2;
				$config['suffix'] = '?' . http_build_query($_GET, '', "&");
				//$config['use_page_numbers'] = TRUE;
				//$config['page_query_string'] = false;
				//$config['query_string_segment'] = '';
				$config['first_page'] = 'Awal';
				$config['last_page'] = 'Akhir';
				$config['next_page'] = '&laquo;';
				$config['prev_page'] = '&raquo;';
				
				
				$config['full_tag_open'] = '<div><ul class="pagination">';
				$config['full_tag_close'] = '</ul></div>';
				$config['first_link'] = '&laquo; First';
				$config['first_tag_open'] = '<li class="prev page">';
				$config['first_tag_close'] = '</li>';
				$config['last_link'] = 'Last &raquo;';
				$config['last_tag_open'] = '<li class="next page">';
				$config['last_tag_close'] = '</li>';
				$config['next_link'] = 'Next &rarr;';
				$config['next_tag_open'] = '<li class="next page">';
				$config['next_tag_close'] = '</li>';
				$config['prev_link'] = '&larr; Previous';
				$config['prev_tag_open'] = '<li class="prev page">';
				$config['prev_tag_close'] = '</li>';
				$config['cur_tag_open'] = '<li class="active"><a href="">';
				$config['cur_tag_close'] = '</a></li>';
				$config['num_tag_open'] = '<li class="page">';
				$config['num_tag_close'] = '</li>';
				
				
				//inisialisasi config
				$this->pagination->initialize($config);
				$halaman = $this->pagination->create_links();
				$list_periode = $this->M_periode->list_periode_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				$data = array('page_content'=>'ptn_admin_periode','halaman'=>$halaman,'list_periode'=>$list_periode);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'admin-login');
			}
		}
	}
	
	public function simpan()
	{
		if (!empty($_POST['stat_edit']))
		{
			$this->M_periode->edit(
			
					$_POST['stat_edit']
					,$_POST['PER_NAMA']
					,$_POST['PER_KATEGORI']
					,$_POST['PER_DARI']
					,$_POST['PER_SAMPAI']
					,$_POST['PER_KET']
					,$this->session->userdata('ses_id_karyawan')
					);
			header('Location: '.base_url().'periode-laporan');
		}
		else
		{
			$this->M_periode->simpan(
					
					$_POST['PER_KODE']
					,$_POST['PER_NAMA']
					,$_POST['PER_KATEGORI']
					,$_POST['PER_DARI']
					,$_POST['PER_SAMPAI']
					,$_POST['PER_KET']
					,$this->session->userdata('ses_id_karyawan')
					,$this->session->userdata('ses_kode_kantor')
					,"KAB"
					);
			header('Location: '.base_url().'periode-laporan');
		}
		
		//echo 'ade';
	}
	
	public function hapus()
	{
		$id = $this->uri->segment(2,0);
		$this->M_periode->hapus($id);
		header('Location: '.base_url().'periode-laporan');
	}
	
	function cek_periode()
	{
		$hasil_cek = $this->M_periode->get_periode('PER_KODE',$_POST['PER_KODE']);
		echo $hasil_cek;
	}
	
	
	
	function table_periode_mingguan()
	{
		
		$list_periode = $this->M_periode->list_periode_limit(" WHERE PER_KATEGORI = 'MINGGUAN'",1000,0);
		if(!empty($list_periode))
		{
			$list_result = $list_periode->result();
			echo'
			<div class="box">
				<div class="box-header">
				  <!-- <h3 class="box-title">Data Table With Full Features</h3> -->
				</div><!-- /.box-header -->
				<div class="box-body">
				  <table id="example1" class="table table-bordered table-striped">
					<thead>
					  <tr>
						<th>KODE</th>
						<th>Nama</th>
						<th>Keterangan</th>
						<th>Berlaku</th>
						<th>Aksi</th>
					  </tr>
					</thead>
					<tbody>';
			foreach($list_result as $row)
			{
				echo'
					
							  <tr>
								<td>'.$row->PER_KODE.'</td>
								<td>'.$row->PER_NAMA.'</td>
								<td>'.$row->PER_KET.'</td>
								<td>'.$row->PER_DARI.' - '.$row->PER_SAMPAI.'</td>
								<td><a class="confirm-btn btn btn-danger btn-sm" href="'.base_url().'periode-laporan-hapus/'.$row->PER_ID.'" title = "Hapus Data '.$row->PER_NAMA.'" alt = "Hapus Data '.$row->PER_NAMA.'">Hapus</a></td>
							  </tr>
							';
			}
			echo'
					</tbody>
					<!-- <tfoot> -->
					  <!-- <tr> -->
						<!-- <th>Rendering engine</th> -->
						<!-- <th>Browser</th> -->
						<!-- <th>Platform(s)</th> -->
						<!-- <th>Engine version</th> -->
						<!-- <th>CSS grade</th> -->
					  <!-- </tr> -->
					<!-- </tfoot> -->
				  </table>
				</div><!-- /.box-body -->
			</div><!-- /.box -->';
		}
	}
	
	
	function table_periode_bulanan()
	{
		
		$list_periode = $this->M_periode->list_periode_limit(" WHERE PER_KATEGORI = 'BULANAN'",1000,0);
		if(!empty($list_periode))
		{
			$list_result = $list_periode->result();
			echo'
			<div class="box">
				<div class="box-header">
				  <!-- <h3 class="box-title">Data Table With Full Features</h3> -->
				</div><!-- /.box-header -->
				<div class="box-body">
				  <table id="example1" class="table table-bordered table-striped">
					<thead>
					  <tr>
						<th>KODE</th>
						<th>Nama</th>
						<th>Keterangan</th>
						<th>Berlaku</th>
						<th>Aksi</th>
					  </tr>
					</thead>
					<tbody>';
			foreach($list_result as $row)
			{
				echo'
					
							  <tr>
								<td>'.$row->PER_KODE.'</td>
								<td>'.$row->PER_NAMA.'</td>
								<td>'.$row->PER_KET.'</td>
								<td>'.$row->PER_DARI.' - '.$row->PER_SAMPAI.'</td>
								<td><a class="confirm-btn btn btn-danger btn-sm" href="'.base_url().'periode-laporan-hapus/'.$row->PER_ID.'" title = "Hapus Data '.$row->PER_NAMA.'" alt = "Hapus Data '.$row->PER_NAMA.'">Hapus</a></td>
							  </tr>
							';
			}
			echo'
					</tbody>
					<!-- <tfoot> -->
					  <!-- <tr> -->
						<!-- <th>Rendering engine</th> -->
						<!-- <th>Browser</th> -->
						<!-- <th>Platform(s)</th> -->
						<!-- <th>Engine version</th> -->
						<!-- <th>CSS grade</th> -->
					  <!-- </tr> -->
					<!-- </tfoot> -->
				  </table>
				</div><!-- /.box-body -->
			</div><!-- /.box -->';
		}
	}
	
	function table_periode_semester()
	{
		
		$list_periode = $this->M_periode->list_periode_limit(" WHERE PER_KATEGORI = 'SEMESTER'",1000,0);
		if(!empty($list_periode))
		{
			$list_result = $list_periode->result();
			echo'
			<div class="box">
				<div class="box-header">
				  <!-- <h3 class="box-title">Data Table With Full Features</h3> -->
				</div><!-- /.box-header -->
				<div class="box-body">
				  <table id="example1" class="table table-bordered table-striped">
					<thead>
					  <tr>
						<th>KODE</th>
						<th>Nama</th>
						<th>Keterangan</th>
						<th>Berlaku</th>
						<th>Aksi</th>
					  </tr>
					</thead>
					<tbody>';
			foreach($list_result as $row)
			{
				echo'
					
							  <tr>
								<td>'.$row->PER_KODE.'</td>
								<td>'.$row->PER_NAMA.'</td>
								<td>'.$row->PER_KET.'</td>
								<td>'.$row->PER_DARI.' - '.$row->PER_SAMPAI.'</td>
								<td><a class="confirm-btn btn btn-danger btn-sm" href="'.base_url().'periode-laporan-hapus/'.$row->PER_ID.'" title = "Hapus Data '.$row->PER_NAMA.'" alt = "Hapus Data '.$row->PER_NAMA.'">Hapus</a></td>
							  </tr>
							';
			}
			echo'
					</tbody>
					<!-- <tfoot> -->
					  <!-- <tr> -->
						<!-- <th>Rendering engine</th> -->
						<!-- <th>Browser</th> -->
						<!-- <th>Platform(s)</th> -->
						<!-- <th>Engine version</th> -->
						<!-- <th>CSS grade</th> -->
					  <!-- </tr> -->
					<!-- </tfoot> -->
				  </table>
				</div><!-- /.box-body -->
			</div><!-- /.box -->';
		}
	}
	
	
	function table_periode_tahunan()
	{
		
		$list_periode = $this->M_periode->list_periode_limit(" WHERE PER_KATEGORI = 'TAHUNAN'",1000,0);
		if(!empty($list_periode))
		{
			$list_result = $list_periode->result();
			echo'
			<div class="box">
				<div class="box-header">
				  <!-- <h3 class="box-title">Data Table With Full Features</h3> -->
				</div><!-- /.box-header -->
				<div class="box-body">
				  <table id="example1" class="table table-bordered table-striped">
					<thead>
					  <tr>
						<th>KODE</th>
						<th>Nama</th>
						<th>Keterangan</th>
						<th>Berlaku</th>
						<th>Aksi</th>
					  </tr>
					</thead>
					<tbody>';
			foreach($list_result as $row)
			{
				echo'
					
							  <tr>
								<td>'.$row->PER_KODE.'</td>
								<td>'.$row->PER_NAMA.'</td>
								<td>'.$row->PER_KET.'</td>
								<td>'.$row->PER_DARI.' - '.$row->PER_SAMPAI.'</td>
								<td><a class="confirm-btn btn btn-danger btn-sm" href="'.base_url().'periode-laporan-hapus/'.$row->PER_ID.'" title = "Hapus Data '.$row->PER_NAMA.'" alt = "Hapus Data '.$row->PER_NAMA.'">Hapus</a></td>
							  </tr>
							';
			}
			echo'
					</tbody>
					<!-- <tfoot> -->
					  <!-- <tr> -->
						<!-- <th>Rendering engine</th> -->
						<!-- <th>Browser</th> -->
						<!-- <th>Platform(s)</th> -->
						<!-- <th>Engine version</th> -->
						<!-- <th>CSS grade</th> -->
					  <!-- </tr> -->
					<!-- </tfoot> -->
				  </table>
				</div><!-- /.box-body -->
			</div><!-- /.box -->';
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */