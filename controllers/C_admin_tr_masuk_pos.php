<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_admin_tr_masuk_pos extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_tr_masuk_pos','M_pos','M_tematik'));
		
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
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "WHERE 
								A.INPOS_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
								AND 
								(
									B.POS_KODE LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR B.POS_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR A.INPOS_PERIODEMNTH LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR A.TEMA_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)	
								";
				}
				else
				{
					$cari = "WHERE A.INPOS_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$this->load->library('pagination');
				$config['first_url'] = site_url('admin-transaksi-masuk-pos?'.http_build_query($_GET));
				$config['base_url'] = site_url('admin-transaksi-masuk-pos/');
				$config['total_rows'] = $this->M_tr_masuk_pos->count_tr_masuk_pos_limit($cari)->JUMLAH;
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
				
				$list_pos = $this->M_pos->list_pos_limit("",10,0);
				$list_tr_masuk_pos = $this->M_tr_masuk_pos->list_tr_masuk_pos_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				$list_tematik = $this->M_tematik->list_tematik_limit('',100,0);
				
				$data = array('page_content'=>'king_admin_tr_masuk_pos','halaman'=>$halaman,'list_tr_masuk_pos'=>$list_tr_masuk_pos,'list_pos' => $list_pos,'list_tematik' => $list_tematik);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'admin-login');
			}
		}
	}
	
	function cek_table_kotak()
	{
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "WHERE A.POS_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (POS_NAMA LIKE '%".$_POST['cari']."%' OR POS_KODE LIKE '%".$_POST['cari']."%')
							";
		}
		else
		{
			$cari = "WHERE A.POS_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."'";
		}
		
		$list_pos = $this->M_pos->list_pos_limit($cari,10,0);
		//echo"ADA";
		
		if(!empty($list_pos))
		{
			echo'<table width="100%" id="example2" class="table table-bordered table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">No</th>';
							echo '<th width="15%">Avatar</th>';
							echo '<th width="15%">Kode</th>';
							echo '<th width="25%">Nama</th>';
							echo '<th width="35%">Lokasi</th>';
							echo '<th width="5%">Aksi</th>';
				echo '</tr>
</thead>';
				$list_result = $list_pos->result();
				$no2 =1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					echo'<tr>';
						echo'<td><input type="hidden" id="no_'.$row->POS_ID.'" value="'.$row->POS_ID.'" />'.$no2.'</td>';
						if ($row->IMG_LINK == "")
						{
							$src = base_url().'assets/global/karyawan/loading.gif';
							echo '<td><img id="img_'.$no2.'"  width="75px" height="75px" style="border:1px solid #C8C8C8; padding:5px; float:left; margin-right:20px;" src="'.$src.'" /></td>';
							
							echo'<input type="hidden" id="avatar_url_'.$no2.'" value="'.$src.'" />';
						}
						else
						{
							$src = base_url().'assets/global/karyawan/'.$row->IMG_IMAGE;
							echo '<td><img id="img_'.$no2.'"  width="75px" height="75px" style="border:1px solid #C8C8C8; padding:5px; float:left; margin-right:20px;" src="'.$src.'" /></td>';
							
							echo'<input type="hidden" id="avatar_url_'.$no2.'" value="'.$src.'" />';
						}
						echo'<td><input type="hidden" id="POS_KODE2_'.$no2.'" value="'.$row->POS_KODE.'" />'.$row->POS_KODE.'</td>';
						echo'<td><input type="hidden" id="POS_NAMA2_'.$no2.'" value="'.$row->POS_NAMA.'" />'.$row->POS_NAMA.'</td>';
						echo'<td><input type="hidden" id="POS_ALMTDETAIL2_'.$no2.'" value="'.$row->POS_ALMTDETAIL.'" />'.$row->POS_ALMTDETAIL.'</td>';
						
						echo'<input type="hidden" id="POS_ID2_'.$no2.'" value="'.$row->POS_ID.'" />';
						
						echo'<td>
							<button type="button" onclick="insert('.$no2.')" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#myModal">Pilih</button>
						</td>';
						
					echo'</tr>';
					$no2++;
				}
				
				echo '</tbody>';
			echo'</table>';
		}
	}
	
	public function simpan()
	{
		if (!empty($_POST['stat_edit']))
		{	
			$this->M_tr_masuk_pos->edit
			(
				$_POST['stat_edit'],
				$_POST['POS_ID'],
				$_POST['TEMA_NAMA'],
				$_POST['INPOS_PERIODETHN'],
				$_POST['INPOS_PERIODEMNTH'],
				$_POST['INPOS_PETUGAS'],
				$_POST['INPOS_DITERIMA'],
				$_POST['INPOS_DTTRAN'],
				$_POST['INPOS_LOKASI'],
				$_POST['INPOS_NOMINAL'],
				$_POST['INPOS_KET'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			header('Location: '.base_url().'admin-transaksi-masuk-pos');
			
			
		}
		else
		{
			$this->M_tr_masuk_pos->simpan
			(
			
				$_POST['POS_ID'],
				$_POST['TEMA_NAMA'],
				$_POST['INPOS_PERIODETHN'],
				$_POST['INPOS_PERIODEMNTH'],
				$_POST['INPOS_PETUGAS'],
				$_POST['INPOS_DITERIMA'],
				$_POST['INPOS_DTTRAN'],
				$_POST['INPOS_LOKASI'],
				$_POST['INPOS_NOMINAL'],
				$_POST['INPOS_KET'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			header('Location: '.base_url().'admin-transaksi-masuk-pos');
		}
		
		//echo 'ade';*/
	}
	
	public function hapus()
	{
		$INPOS_ID = $this->uri->segment(2,0);
		$this->M_tr_masuk_pos->hapus($this->session->userdata('ses_kode_kantor'),$INPOS_ID);
		
			$this->M_log->simpan(
				"DELETE",
				$this->session->userdata('ses_nama_karyawan'),
				"Melakukan penghapusan Uang Masuk Dari Kotak Amal dengan id : ".$KART_ID." ",
				$this->session->userdata('ses_kode_kantor')
			);
		
		header('Location: '.base_url().'admin-transaksi-masuk-pos');
	}
	
	function cek_tr_masuk_pos()
	{	
		$INPOS_NAMA = $_POST['INPOS_NAMA'];
		$hasil_cek = $this->M_tr_masuk_pos->get_tr_tb_masuk_pos('INPOS_NAMA',$INPOS_NAMA);
		echo $hasil_cek;
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */