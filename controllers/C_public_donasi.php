<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_public_donasi extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_amal','M_kat_amal','M_images','M_bank','M_tr_masuk_muzaqi'));
		
	}
	
	public function index()
	{
		
			
		// $data = array('page_content'=>'king_jabatan');
		// $this->load->view('admin/container',$data);
		if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
		{
			$cari = "WHERE A.AML_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'";
		}
		else
		{
			$cari = "";
		}
		
		$this->load->library('pagination');
		//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
		//$config['base_url'] = base_url().'admin/jabatan/';
		
		$config['first_url'] = site_url('donasi-sesama?'.http_build_query($_GET));
		$config['base_url'] = site_url('donasi-sesama/');
		$config['total_rows'] = $this->M_amal->count_amal_limit($cari)->JUMLAH;
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
		
		
		$list_amal_terbatas = $this->M_amal->list_amal_limit('',2,0);
		
		$list_amal = $this->M_amal->list_amal_limit($cari,$config['per_page'],$this->uri->segment(4,0));
		$title = "SISTEM TERINTEGRASI BAZNAS SUKABUMI | Ajakan Donasi Sesama BAZNAS Kabupaten Sukabumi";
		
		$data = array('page_content'=>'list_donasi','halaman'=>$halaman,'list_amal'=>$list_amal,'title'=>$title,'list_amal_terbatas' => $list_amal_terbatas,'cari' => $cari);
		//$this->load->view('admin/container',$data);
		$this->load->view('public/container.html',$data);
			
		
	}
	
	function detail_amal()
	{
		$AML_LINKTITLE = $this->uri->segment(4,0);
		$data_amal = $this->M_amal->list_amal_limit(" WHERE A.AML_LINKTITLE = '".$AML_LINKTITLE."' ",1,0);
		if(!empty($data_amal))
		{
			//echo $AML_LINKTITLE;
			$data_amal = $data_amal->row();
			$terbilang = $this->Terbilang($data_amal->AML_NOMINAL);
			$list_bank = $this->M_bank->list_bank_limit("",10,0);
			
			
			$list_tr_masuk_muzaqi = $this->M_tr_masuk_muzaqi->list_tr_masuk_muzaqi_limit(" WHERE A.AML_ID = '".$data_amal->AML_ID."'",100,0);
			
			$data = array('page_content'=>'detail_donasi','desc'=> word_limiter($data_amal->AML_KET,25),'data_amal'=>$data_amal,'title'=> "BAZNAS Kab. Sukabumi | ".$data_amal->AML_NAMA,'sidebar'=>1,'images_icon' => $data_amal->IMG_LINK,'terbilang'=>$terbilang,'list_bank' => $list_bank,'list_tr_masuk_muzaqi' => $list_tr_masuk_muzaqi);
			$this->load->view('public/container.html',$data);
		}
		else
		{
			//echo $AML_LINKTITLE;
			header('Location: '.base_url());
		}
	}
	
	public function simpan()
	{
			$this->M_tr_masuk_muzaqi->simpan
			(
			
				$_POST['MUZ_ID'],
				'',
				$_POST['AML_ID'],
				'DONASI',
				$_POST['INMUZ_BANK'],
				$_POST['INMUZ_ATASNAMA'],
				$_POST['INMUZ_NOREK'],
				$_POST['INMUZ_NOMINAL'],
				$_POST['INMUZ_DTTRAN'],
				0,
				'',
				'',
				'',//$this->session->userdata('ses_id_karyawan'),
				'',//$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_MUZ_KODEKANTOR')
			);
			
			$data_amal = $this->M_amal->list_amal_limit(" WHERE A.AML_ID = '".$_POST['AML_ID']."' ",1,0);
			if(!empty($data_amal))
			{
				$data_amal = $data_amal->row();
			
				$this->load->library('email');
				$this->load->config('email');
				$this->email->set_mailtype("html");
				$this->email->set_newline("\r\n");

				//Email content
				$htmlContent = "<h1>BAZNAS Kab.Sukabumi | Terima kasih Atas Donasi Anda</h1>";
				$htmlContent .= '<html><head><head></head><body><p>Assalamualaikum Wr Wb</p><p>Hai '. $this->session->userdata('ses_MUZ_NAMA') .'</p><p>Terima kasih atas donasi yang anda berikan kepada '.$data_amal->AML_NAMA.' Berupa Nominal uang sebanyak</p><p>Rp.'.number_format($_POST['INMUZ_NOMINAL'],0,",",".").' | <i>"'.$this->Terbilang($_POST['INMUZ_NOMINAL']).' Rupiah"</i></p><p>Kami akan melakukan verifikasi untuk donasi yang anda lakukan, setelah terverifikasi kami akan mengirim email pemberitahuan.</p><p>Semoga amal ibadah yang dilakukan diterima dan dibalas oleh Allah SWT</P><img class="img-fluid" src="'.base_url().'assets/global/karyawan/donasiTerimaKasih.png" alt="" /><p>Hormat kami,</p><p>Tim BAZNAS Kab.Sukabumi</p><p>
مَثَلُ الَّذِيْنَ يُنْفِقُوْنَ اَمْوَالَهُمْ فِيْ سَبِيْلِ اللّٰهِ  كَمَثَلِ حَبَّةٍ اَنْۢبَتَتْ سَبْعَ سَنَابِلَ فِيْ كُلِّ سُنْۢبُلَةٍ مِّائَةُ حَبَّةٍ ۗ   وَاللّٰهُ يُضٰعِفُ لِمَنْ يَّشَآءُ  ۗ  وَاللّٰهُ وَاسِعٌ عَلِيْمٌ
"Perumpamaan orang yang menginfakkan hartanya di jalan Allah seperti sebutir biji yang menumbuhkan tujuh tangkai, pada setiap tangkai ada seratus biji. Allah melipatgandakan bagi siapa yang Dia kehendaki, dan Allah Maha Luas, Maha Mengetahui."
(QS. Al-Baqarah 2: Ayat 261)</p></body></html>';

				$this->email->to($this->session->userdata('ses_MUZ_EMAIL'));
				//$this->email->to('baznas.kabsukabumi2018@gmail.com');
				$this->email->from('admin@kabsukabumi.baznas.go.id','BAZNAS Kab.Sukabumi');
				$this->email->subject('BAZNAS Kab.Sukabumi | Terima kasih Atas Donasi Anda (Do not reply)');
				$this->email->message($htmlContent);

				//Send email
				//$this->email->send();
				
				if($this->email->send())
				{
					//BERHASIL
					header('Location: '.$_POST['LINK_URL']);
				} else {
					//GAGAL
					header('Location: '.$_POST['LINK_URL']);
				}
			}	
			
	}
	
	function hapus_donasi()
	{
		$AML_ID = $this->uri->segment(2,0);
		$INMUZ_ID = $this->uri->segment(3,0);
		
		$this->M_tr_masuk_muzaqi->hapus($this->session->userdata('ses_MUZ_KODEKANTOR'),$INMUZ_ID);
		$data_amal = $this->M_amal->list_amal_limit(" WHERE A.AML_ID = '".$AML_ID."' ",1,0);
		$data_amal = $data_amal->row();
		
		header('Location: '.base_url().'detail-donasi/'.$data_amal->AML_LINKTAHUN.'/'.$data_amal->AML_LINKBULAN.'/'.$data_amal->AML_LINKTITLE);
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