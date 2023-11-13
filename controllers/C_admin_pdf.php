<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_admin_pdf extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->library('Fpdf');
		//$this->load->model(array('M_h_pendaftaran','M_kontes','M_d_pendaftaran','M_member','M_penutupan'));
		$this->load->model(array('M_tr_masuk_pos','M_tr_keluar_operasional'));
	}
	
	
	function tanggal($var = '')
	{
	$tgl = array("Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember");
	$pecah = explode("-", $var);
	return $pecah[2]." ".$tgl[$pecah[1] - 1]." ".$pecah[0];
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
	
	function testpdf()
	{
		
		//$this->fpdf->FPDF('P','cm','A4');
		$this->fpdf->AddPage('L','A4');
		$this->fpdf->SetFont('Arial','',10);
		$teks = "Ini hasil Laporan PDF menggunakan Library FPDF di CodeIgniter";
		$this->fpdf->Cell(3, 0.5, $teks, 1, '0', 'L', true);
		// $this->fpdf->Ln();
		$this->fpdf->Output(); 
	}
	
	public function print_bukti_setoran_kotak_amal()
	{
		//CEK DATA INPUT
		$INPOS_ID = $_GET['INPOS_ID'];
		$cari = "WHERE A.INPOS_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' AND A.INPOS_ID = '".$INPOS_ID."'";
		$data_inpos = $this->M_tr_masuk_pos->list_tr_masuk_pos_limit($cari,1,0);
		//CEK DATA INPUT
		if(!empty($data_inpos))
		{
			$data_inpos = $data_inpos->row();
			
			
			
			//HEADER
				$this->fpdf->AddPage();
				$this->fpdf->Image('assets/global/images/logo2.png',5,0,20,20);
				//$this->fpdf->Image('assets/global/images/cjrjago200.png',80,7,10,10);
			
				$this->fpdf->SetFont('Arial','B',10);
				$this->fpdf->Ln(3);
				$this->fpdf->SetY(7); //Set posisi top
				
				
				//$this->fpdf->Cell(5,5,'',0,0,'L',false);
				$x = $this->fpdf->GetX();
				$this->fpdf->Cell(180,5,'BUKTI SETOR',0,1,'C',false);
				//$this->fpdf->Cell(120,5,'',0,0,'L',false);$this->fpdf->Cell(100,5,$data_kontes->mulai.' - '.$data_kontes->sampai,0,1,'L',false);
				
				//$this->fpdf->Cell(5,5,'',0,0,'L',false);
				$this->fpdf->Cell(180,5,'DIVISI INFAK SEDEKAH DAN MEDIA INFORMASI',0,1,'C',false);
				
				$this->fpdf->Ln(3);
				$this->fpdf->Line(5,20,200,20);
				
				$this->fpdf->SetFont('Arial','',6);
				$this->fpdf->SetY(22); //Set posisi top
				$this->fpdf->Cell(180,5,date('Y-m-d h:m:s'),0,1,'R',false);
				
				$this->fpdf->SetFont('times','',10);
				$this->fpdf->Cell(25,5,"Tanggal",0,0,'L',false);$this->fpdf->Cell(3,5,":",0,0,'L',false);$this->fpdf->Cell(50,5,$data_inpos->INPOS_DTTRAN,0,1,'L',false);
				$this->fpdf->Cell(25,5,"Nama Collector",0,0,'L',false);$this->fpdf->Cell(3,5,":",0,0,'L',false);$this->fpdf->Cell(50,5,$data_inpos->INPOS_PETUGAS,0,1,'L',false);
				
				$this->fpdf->Cell(25,5,"Alamat Outlet",0,0,'L',false);$this->fpdf->Cell(3,5,":",0,0,'L',false);
				//$this->fpdf->Cell(5,5,$data_inpos->perihal,0,1,'L',false);
				$x = $this->fpdf->GetX();
				//$this->fpdf->vcell(50,5,$x,$data_inpos->perihal);
				$this->fpdf->vcell(50,5,$x,$data_inpos->POS_NAMA);
				$this->fpdf->Cell(5,5,"",0,1,'L',false);
				
				
				
				$this->fpdf->Cell(25,5,"Jumlah Penarika",0,0,'L',false);$this->fpdf->Cell(3,5,":",0,0,'L',false);$this->fpdf->Cell(50,5,"Rp.".number_format($data_inpos->INPOS_NOMINAL,0,",","."),0,1,'L',false);
				
				$this->fpdf->SetFont('Arial','I',8);
				$this->fpdf->Cell(150,10,"Terbilang : ".$this->Terbilang($data_inpos->INPOS_NOMINAL)." Rupiah",0,1,'L',false);
				
				// if(file_exists("assets/global/images/qrcode/".$data_pengajaun->no_pengajuan.".png"))
				// {
					// $this->fpdf->Image("assets/global/images/qrcode/".$data_pengajaun->no_pengajuan.".png",35,55,25,25);
				// }
				
				// $this->fpdf->SetY(80); //Set posisi top
				// $this->fpdf->SetFont('Arial','B',12);
				// $this->fpdf->Cell(75,5,$data_pengajaun->no_pengajuan,0,1,'C',false);
				
				// $this->fpdf->SetFont('Arial','',6);
				// $this->fpdf->Cell(50,3,"Printed : ".$this->session->userdata('ses_nama_karyawan'),0,1,'L',false);
				
				$this->fpdf->SetFont('Arial','',7);
				
				
				$this->fpdf->Cell(100,5,'Clerk & Cashier',0,0,'C',false);$this->fpdf->Cell(100,5,'Collector',0,1,'C',false);
				$this->fpdf->Cell(5,5,"",0,1,'L',false);
				$this->fpdf->Cell(5,5,"",0,1,'L',false);
				$this->fpdf->Cell(100,5,$this->session->userdata('ses_nama_karyawan'),0,0,'C',false);$this->fpdf->Cell(100,5,'Collector',0,1,'C',false);
				
				
				$this->fpdf->Ln(3);
				$this->fpdf->Line(5,88,200,88);
				// $this->fpdf->Cell(75,3,'Simpan slip ini sebagai bukti pengajuan dokumen',0,1,'C',false);
				// $this->fpdf->Cell(75,3,'untuk melihat status pengajuan, silahkan download',0,1,'C',false);
				// $this->fpdf->Cell(75,3,'aplikasi naskah dinas cianjur di playstore',0,1,'C',false);
				
				
			//HEADER
			
			$this->fpdf->Output($data_inpos->INPOS_ID.'.pdf','D');
		}
		else
		{
			header('Location: '.base_url().'admin-transaksi-masuk-pos');
		}
	}
	
	
	public function print_bukti_keluar_operasional()
	{
		//CEK DATA INPUT
		$OTOPRS_ID = $_GET['OTOPRS_ID'];
		$cari = "WHERE A.OTOPRS_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' AND A.OTOPRS_ID = '".$OTOPRS_ID."'";
		$data_operasioanl = $this->M_tr_keluar_operasional->list_tr_keluar_operasional_limit($cari,1,0);
		//CEK DATA INPUT
		if(!empty($data_operasioanl))
		{
			$data_operasioanl = $data_operasioanl->row();
			
			
			
			//HEADER
				$this->fpdf->AddPage();
				$this->fpdf->Image('assets/global/images/logo2.png',65,0,15,15);
				//$this->fpdf->Image('assets/global/images/cjrjago200.png',80,7,10,10);
			
				$this->fpdf->SetFont('Arial','B',8);
				$this->fpdf->Ln(3);
				$this->fpdf->SetY(7); //Set posisi top
				
				$this->fpdf->Cell(28,5,"NO","LT",0,'L',false);
				$this->fpdf->Cell(3,5,":","T",0,'L',false);
				$this->fpdf->Cell(20,5,"","TR",0,'L',false);
				$this->fpdf->Cell(110,5,"KWITANSI",0,0,'C',false);
				$this->fpdf->Cell(30,5,"NO.".$data_operasioanl->OTOPRS_NOMOR,1,1,'L',false);
				
				$this->fpdf->Cell(28,5,"Telah Terima Dari","L",0,'L',false);
				$this->fpdf->Cell(3,5,":",0,0,'L',false);
				$this->fpdf->Cell(20,5,"","R",0,'L',false);
				$this->fpdf->Cell(110,5,"",0,0,'C',false);
				$this->fpdf->Cell(30,5,"",0,1,'L',false);
				
				$this->fpdf->Cell(28,5,"","L",0,'L',false);
				$this->fpdf->Cell(3,5,"",0,0,'L',false);
				$this->fpdf->Cell(20,5,"","R",0,'L',false);
				$this->fpdf->Cell(40,5,"Telah Terima Dari",0,0,'L',false);
				$this->fpdf->Cell(5,5,":",0,0,'L',false);
				$this->fpdf->Cell(85,5,$data_operasioanl->OTOPRS_DISERAHKAN ,0,0,'L',false);
				$this->fpdf->Cell(30,5,"",0,1,'L',false);
				
				
				
				$this->fpdf->Cell(28,5,"Uang Sejumlah","L",0,'L',false);
				$this->fpdf->Cell(3,5,":",0,0,'L',false);
				$this->fpdf->Cell(20,5,"","R",0,'L',false);
				$this->fpdf->Cell(40,5,"Uang Sejumlah",0,0,'L',false);
				$this->fpdf->Cell(5,5,":",0,0,'L',false);
				//$this->fpdf->Cell(85,5,"Rp.".number_format($data_operasioanl->OTOPRS_NOMINAL,0,",","."),0,0,'L',false);
				$this->fpdf->Cell(85,5,$this->Terbilang($data_operasioanl->OTOPRS_NOMINAL)." Rupiah",0,0,'L',false);
				$this->fpdf->Cell(30,5,"",0,1,'L',false);
				
				$this->fpdf->Cell(28,5,"","L",0,'L',false);
				$this->fpdf->Cell(3,5,"",0,0,'L',false);
				$this->fpdf->Cell(20,5,"","R",0,'L',false);
				$this->fpdf->Cell(40,5,"Untuk Pembayaran",0,0,'L',false);
				$this->fpdf->Cell(5,5,":",0,0,'L',false);
				$this->fpdf->Cell(85,5,$data_operasioanl->OTOPRS_PERIHAL ,0,0,'L',false);
				$this->fpdf->Cell(30,5,"",0,1,'L',false);
				
				$this->fpdf->Cell(28,5,"Untuk Pembayaran","L",0,'L',false);
				$this->fpdf->Cell(3,5,":",0,0,'L',false);
				$this->fpdf->Cell(20,5,"","R",0,'L',false);
				$this->fpdf->Cell(10,5,"",0,0,'L',false);
				$this->fpdf->Cell(40,5,"Rp.".number_format($data_operasioanl->OTOPRS_NOMINAL,0,",","."),0,0,'L',false);
				$this->fpdf->Cell(80,5,"",0,0,'L',false);
				//$this->fpdf->Cell(85,5,$this->Terbilang($data_operasioanl->OTOPRS_NOMINAL)." Rupiah",0,0,'L',false);
				$this->fpdf->Cell(30,5,"",0,1,'L',false);
				
				$this->fpdf->Cell(28,5,"","L",0,'L',false);
				$this->fpdf->Cell(3,5,"",0,0,'L',false);
				$this->fpdf->Cell(20,5,"","R",0,'L',false);
				$this->fpdf->Cell(110,5,"",0,0,'C',false);
				$this->fpdf->Cell(30,5,"Sukabumi, ".date("Y-m-d"),0,1,'L',false);
				
				$this->fpdf->Cell(28,5,"Rp","L",0,'R',false);
				$this->fpdf->Cell(3,5,":",0,0,'L',false);
				$this->fpdf->Cell(20,5,number_format($data_operasioanl->OTOPRS_NOMINAL,0,",","."),"R",0,'L',false);
				$this->fpdf->Cell(40,5,"",0,0,'L',false);
				$this->fpdf->Cell(5,5,"",0,0,'L',false);
				//$this->fpdf->Cell(85,5,"Rp.".number_format($data_operasioanl->OTOPRS_NOMINAL,0,",","."),0,0,'L',false);
				$this->fpdf->Cell(85,5,"",0,0,'L',false);
				$this->fpdf->Cell(30,5,"",0,1,'L',false);
				
				$this->fpdf->Cell(28,5,"","LB",0,'R',false);
				$this->fpdf->Cell(3,5,"","B",0,'L',false);
				$this->fpdf->Cell(20,5,"","BR",0,'L',false);
				$this->fpdf->Cell(40,5,"Diketahui",0,0,'C',false);
				$this->fpdf->Cell(40,5,"Disetujui",0,0,'C',false);
				$this->fpdf->Cell(40,5,"Penerima",0,1,'C',false);
				//$this->fpdf->Cell(5,5,"",0,0,'L',false);
				//$this->fpdf->Cell(85,5,"Rp.".number_format($data_operasioanl->OTOPRS_NOMINAL,0,",","."),0,0,'L',false);
				//$this->fpdf->Cell(85,5,"",0,0,'L',false);
				//$this->fpdf->Cell(30,5,"",0,1,'L',false);
				
				$this->fpdf->Cell(200,5,"",0,1,'C',false);
				$this->fpdf->Cell(200,5,"",0,1,'C',false);
				$this->fpdf->Cell(200,5,"",0,1,'C',false);
				
				
				$this->fpdf->Cell(28,5,"",0,0,'R',false);
				$this->fpdf->Cell(3,5,"",0,0,'L',false);
				$this->fpdf->Cell(20,5,"",0,0,'L',false);
				$this->fpdf->Cell(40,5,$this->session->userdata('ses_nama_karyawan'),0,0,'C',false);
				$this->fpdf->Cell(40,5,".....................",0,0,'C',false);
				$this->fpdf->Cell(40,5,".....................",0,1,'C',false);
				
				
				
			//HEADER
			
			$this->fpdf->Output($data_operasioanl->OTOPRS_ID.'.pdf','D');
		}
		else
		{
			header('Location: '.base_url().'admin-transaksi-masuk-pos');
		}
	}
	
	public function print_faktur_pengajuan()
	{
		$no_pengajuan = $_GET['pengajuan'];
		// echo $id_member;
		//DATA PENGAJUAN
			$data_pengajaun = $this->M_pengajuan->get_pengajuan('A.no_pengajuan',$no_pengajuan);
		//DATA PENGAJUAN
		if(!empty($data_pengajaun))
		{
			$data_pengajaun = $data_pengajaun->row();
			
			
			
			//HEADER
				$this->fpdf->AddPage();
				$this->fpdf->Image('assets/global/images/logo.png',5,7,10,10);
				$this->fpdf->Image('assets/global/images/cjrjago200.png',80,7,10,10);
			
				$this->fpdf->SetFont('Arial','B',10);
				$this->fpdf->Ln(3);
				$this->fpdf->SetY(7); //Set posisi top
				
				
				//$this->fpdf->Cell(5,5,'',0,0,'L',false);
				$x = $this->fpdf->GetX();
				$this->fpdf->Cell(75,5,'PEMERINTAHAN CIANJUR',0,1,'C',false);
				//$this->fpdf->Cell(120,5,'',0,0,'L',false);$this->fpdf->Cell(100,5,$data_kontes->mulai.' - '.$data_kontes->sampai,0,1,'L',false);
				
				//$this->fpdf->Cell(5,5,'',0,0,'L',false);
				$this->fpdf->Cell(75,5,'SEKPRI BUPATI',0,1,'C',false);
				
				$this->fpdf->Ln(3);
				$this->fpdf->Line(5,20,90,20);
				
				$this->fpdf->SetFont('Arial','',6);
				$this->fpdf->SetY(22); //Set posisi top
				$this->fpdf->Cell(80,5,date('Y-m-d h:m:s'),0,1,'R',false);
				
				$this->fpdf->SetFont('times','',10);
				$this->fpdf->Cell(25,5,"Jenis Dokumen",0,0,'L',false);$this->fpdf->Cell(3,5,":",0,0,'L',false);$this->fpdf->Cell(50,5,$data_pengajaun->nama_jenis_naskah,0,1,'L',false);
				$this->fpdf->Cell(25,5,"Asal Dokumen",0,0,'L',false);$this->fpdf->Cell(3,5,":",0,0,'L',false);$this->fpdf->Cell(50,5,$data_pengajaun->sumber,0,1,'L',false);
				
				$this->fpdf->Cell(25,5,"Perihal",0,0,'L',false);$this->fpdf->Cell(3,5,":",0,0,'L',false);
				//$this->fpdf->Cell(5,5,$data_pengajaun->perihal,0,1,'L',false);
				$x = $this->fpdf->GetX();
				//$this->fpdf->vcell(50,5,$x,$data_pengajaun->perihal);
				$this->fpdf->vcell(50,10,$x,$data_pengajaun->perihal);
				
				
				
				$this->fpdf->Cell(25,5,"Tanggal Terima",0,0,'L',false);$this->fpdf->Cell(3,5,":",0,0,'L',false);$this->fpdf->Cell(50,5,$data_pengajaun->tgl_surat_masuk,0,1,'L',false);
				
				if(file_exists("assets/global/images/qrcode/".$data_pengajaun->no_pengajuan.".png"))
				{
					$this->fpdf->Image("assets/global/images/qrcode/".$data_pengajaun->no_pengajuan.".png",35,55,25,25);
				}
				
				$this->fpdf->SetY(80); //Set posisi top
				$this->fpdf->SetFont('Arial','B',12);
				$this->fpdf->Cell(75,5,$data_pengajaun->no_pengajuan,0,1,'C',false);
				
				$this->fpdf->SetFont('Arial','',6);
				$this->fpdf->Cell(50,3,"Printed : ".$this->session->userdata('ses_nama_karyawan'),0,1,'L',false);
				
				$this->fpdf->SetFont('Arial','',7);
				$this->fpdf->Ln(3);
				$this->fpdf->Line(5,88,90,88);
				
				$this->fpdf->Cell(75,3,'Simpan slip ini sebagai bukti pengajuan dokumen',0,1,'C',false);
				$this->fpdf->Cell(75,3,'untuk melihat status pengajuan, silahkan download',0,1,'C',false);
				$this->fpdf->Cell(75,3,'aplikasi naskah dinas cianjur di playstore',0,1,'C',false);
				
				
			//HEADER
			
			$this->fpdf->Output($no_pengajuan.'.pdf','D');
		}
		else
		{
			header('Location: '.base_url().'admin-member');
		}
	}
	
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_pdf.php */