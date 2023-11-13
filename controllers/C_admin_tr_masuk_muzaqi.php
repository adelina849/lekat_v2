<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_admin_tr_masuk_muzaqi extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_tr_masuk_muzaqi','M_amal','M_muzaqi','M_tematik'));
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
								A.INMUZ_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
								AND 
								(
									B.POS_KODE LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR B.POS_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR A.INMUZ_PERIODEMNTH LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR A.TEMA_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)	
								";
				}
				else
				{
					$cari = "WHERE A.INMUZ_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$this->load->library('pagination');
				$config['first_url'] = site_url('admin-transaksi-masuk-pos?'.http_build_query($_GET));
				$config['base_url'] = site_url('admin-transaksi-masuk-pos/');
				$config['total_rows'] = $this->M_tr_masuk_muzaqi->count_tr_masuk_muzaqi_limit($cari)->JUMLAH;
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
				
				$list_muzaqi = $this->M_muzaqi->list_muzaqi_limit("",10,0);
				$list_tr_masuk_muzaqi = $this->M_tr_masuk_muzaqi->list_tr_masuk_muzaqi_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				
				$data = array('page_content'=>'king_admin_tr_masuk_muzaqi','halaman'=>$halaman,'list_tr_masuk_muzaqi'=>$list_tr_masuk_muzaqi,'list_muzaqi' => $list_muzaqi);
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
			$cari = "WHERE A.MUZ_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (A.MUZ_NAMA LIKE '%".$_POST['cari']."%' OR A.MUZ_KODE LIKE '%".$_POST['cari']."%')
							";
		}
		else
		{
			$cari = "WHERE A.MUZ_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."'";
		}
		
		$list_muzaqi = $this->M_muzaqi->list_muzaqi_limit($cari,10,0);
		//echo"ADA";
		
		if(!empty($list_muzaqi))
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
				$list_result = $list_muzaqi->result();
				$no2 =1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					echo'<tr>';
						echo'<td><input type="hidden" id="no_'.$row->MUZ_ID.'" value="'.$row->MUZ_ID.'" />'.$no2.'</td>';
						if ($row->MUZ_AVATAR == "")
						{
							$src = base_url().'assets/global/karyawan/loading.gif';
							echo '<td><img id="img_'.$no2.'"  width="75px" height="75px" style="border:1px solid #C8C8C8; padding:5px; float:left; margin-right:20px;" src="'.$src.'" /></td>';
							
							echo'<input type="hidden" id="avatar_url_'.$no2.'" value="'.$src.'" />';
						}
						else
						{
							$src = base_url().'assets/global/users/'.$row->MUZ_AVATAR;
							echo '<td><img id="img_'.$no2.'"  width="75px" height="50px" style="border:1px solid #C8C8C8; padding:5px; float:left; margin-right:20px;" src="'.$src.'" /></td>';
							
							echo'<input type="hidden" id="avatar_url_'.$no2.'" value="'.$src.'" />';
						}
						echo'<td><input type="hidden" id="MUZ_KODE2_'.$no2.'" value="'.$row->MUZ_KODE.'" />'.$row->MUZ_KODE.'</td>';
						echo'<td><input type="hidden" id="MUZ_NAMA2_'.$no2.'" value="'.$row->MUZ_NAMA.'" />'.$row->MUZ_NAMA.'</td>';
						echo'<td><input type="hidden" id="MUZ_ALMTDETAIL2_'.$no2.'" value="'.$row->MUZ_ALMTDETAIL.'" />'.$row->MUZ_ALMTDETAIL.'</td>';
						
						echo'<input type="hidden" id="MUZ_ID2_'.$no2.'" value="'.$row->MUZ_ID.'" />';
						
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
			$this->M_tr_masuk_muzaqi->edit
			(
				$_POST['stat_edit'],
				$_POST['MUZ_ID'],
				'',
				'',
				$_POST['INMUZ_KAT'],
				$_POST['INMUZ_BANK'],
				$_POST['INMUZ_ATASNAMA'],
				$_POST['INMUZ_NOREK'],
				$_POST['INMUZ_NOMINAL'],
				$_POST['INMUZ_DTTRAN'],
				'',
				'',
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			
			);
			header('Location: '.base_url().'admin-transaksi-masuk-muzaqi');
			
			
		}
		else
		{
			$this->M_tr_masuk_muzaqi->simpan
			(
			
				$_POST['MUZ_ID'],
				'',
				'',
				$_POST['INMUZ_KAT'],
				$_POST['INMUZ_BANK'],
				$_POST['INMUZ_ATASNAMA'],
				$_POST['INMUZ_NOREK'],
				$_POST['INMUZ_NOMINAL'],
				$_POST['INMUZ_DTTRAN'],
				0,
				'',
				'',
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			header('Location: '.base_url().'admin-transaksi-masuk-muzaqi');
		}
		
		//echo 'ade';*/
	}
	
	public function hapus()
	{
		$INMUZ_ID = $this->uri->segment(2,0);
		$this->M_tr_masuk_muzaqi->hapus($this->session->userdata('ses_kode_kantor'),$INMUZ_ID);
		
			$this->M_log->simpan(
				"DELETE",
				$this->session->userdata('ses_nama_karyawan'),
				"Melakukan penghapusan Uang Masuk Dari Kotak Amal dengan id : ".$KART_ID." ",
				$this->session->userdata('ses_kode_kantor')
			);
		
		header('Location: '.base_url().'admin-transaksi-masuk-muzaqi');
	}
	
	public function verified()
	{
		$INMUZ_ID = $this->uri->segment(2,0);
		$this->M_tr_masuk_muzaqi->verified($INMUZ_ID,$this->session->userdata('ses_kode_kantor'));
		
		$data_muzaki_dan_donasi = $this->M_tr_masuk_muzaqi->list_tr_masuk_muzaqi_limit(" WHERE A.INMUZ_ID = '".$INMUZ_ID."' ",1,0);
		$data_muzaki_dan_donasi = $data_muzaki_dan_donasi->row();
		
		if($data_muzaki_dan_donasi->INMUZ_VERIFIED == 1)
		{
			$data_amal = $this->M_amal->list_amal_limit(" WHERE A.AML_ID = '".$data_muzaki_dan_donasi->AML_ID."' ",1,0);
			$data_amal = $data_amal->row();
			
			$this->load->library('email');
			$this->load->config('email');
			$this->email->set_mailtype("html");
			$this->email->set_newline("\r\n");

			//Email content
			$htmlContent = "<h1>BAZNAS Kab.Sukabumi | Verifikasi Donasi | Terima kasih Atas Donasi Anda</h1>";
			$htmlContent .= '<html><head><head></head><body><p>Assalamualaikum Wr Wb</p><p>Hai '. $data_muzaki_dan_donasi->MUZ_NAMA .'</p><p>Terima kasih atas donasi yang anda berikan kepada '.$data_amal->AML_NAMA.' Berupa Nominal uang sebanyak</p><p>Rp.'.number_format($data_muzaki_dan_donasi->INMUZ_NOMINAL,0,",",".").' | <i>"'.$this->Terbilang($data_muzaki_dan_donasi->INMUZ_NOMINAL).' Rupiah"</i></p><p>Kami telah melakukan verifikasi untuk donasi yang anda lakukan, kami menerima sebagai amanah dan akan kami salurkan kepada yang berhak</p><p>Semoga amal ibadah yang dilakukan diterima dan dibalas oleh Allah SWT</P><img class="img-fluid" src="'.base_url().'assets/global/karyawan/donasiTerimaKasih.png" alt="" /><p>Hormat kami,</p><p>Tim BAZNAS Kab.Sukabumi</p><p>
	مَثَلُ الَّذِيْنَ يُنْفِقُوْنَ اَمْوَالَهُمْ فِيْ سَبِيْلِ اللّٰهِ  كَمَثَلِ حَبَّةٍ اَنْۢبَتَتْ سَبْعَ سَنَابِلَ فِيْ كُلِّ سُنْۢبُلَةٍ مِّائَةُ حَبَّةٍ ۗ   وَاللّٰهُ يُضٰعِفُ لِمَنْ يَّشَآءُ  ۗ  وَاللّٰهُ وَاسِعٌ عَلِيْمٌ</p><p>
	"Perumpamaan orang yang menginfakkan hartanya di jalan Allah seperti sebutir biji yang menumbuhkan tujuh tangkai, pada setiap tangkai ada seratus biji. Allah melipatgandakan bagi siapa yang Dia kehendaki, dan Allah Maha Luas, Maha Mengetahui."
	(QS. Al-Baqarah 2: Ayat 261)</p></body></html>';

			$this->email->to($data_muzaki_dan_donasi->MUZ_EMAIL);
			//$this->email->to('baznas.kabsukabumi2018@gmail.com');
			$this->email->from('admin@kabsukabumi.baznas.go.id','BAZNAS Kab.Sukabumi');
			$this->email->subject('BAZNAS Kab.Sukabumi | Verifikasi Donasi | Terima kasih Atas Donasi Anda (Do not reply)');
			$this->email->message($htmlContent);

			//Send email
			//$this->email->send();
			
			if($this->email->send())
			{
				//BERHASIL
				//header('Location: '.$_POST['LINK_URL']);
			} else {
				//GAGAL
				//header('Location: '.$_POST['LINK_URL']);
			}
		}
		
		
		header('Location: '.base_url().'admin-transaksi-masuk-muzaqi');
	}
	
	function cek_tr_masuk_muzaqi()
	{	
		$INMUZ_NAMA = $_POST['INMUZ_NAMA'];
		$hasil_cek = $this->M_tr_masuk_muzaqi->get_tr_tb_masuk_muzaqi('INMUZ_NAMA',$INMUZ_NAMA);
		echo $hasil_cek;
	}
	
	function Terbilang($x)
	{
	  $abil = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
	  if ($x < 12)
		return " " . $abil[$x];
	  elseif ($x < 20)
		return $this->Terbilang($x - 10) . " Belas";
	  elseif ($x < 100)
		return $this->Terbilang($x / 10) . " Puluh" . $this->Terbilang($x % 10);
	  elseif ($x < 200)
		return " Seratus" . $this->Terbilang($x - 100);
	  elseif ($x < 1000)
		return $this->Terbilang($x / 100) . " Ratus" . $this->Terbilang($x % 100);
	  elseif ($x < 2000)
		return " Seribu" . $this->Terbilang($x - 1000);
	  elseif ($x < 1000000)
		return $this->Terbilang($x / 1000) . " Ribu" . $this->Terbilang($x % 1000);
	  elseif ($x < 1000000000)
		return $this->Terbilang($x / 1000000) . " Juta" . $this->Terbilang($x % 1000000);
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */