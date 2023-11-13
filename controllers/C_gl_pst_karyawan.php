<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_pst_karyawan extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_pst_karyawan','M_gl_lap_apoteker','M_gl_keluarga','M_gl_sekolah','M_gl_karyawan_training','M_gl_karyawan_jabatan','M_gl_punish','M_gl_pengaturan'));
		
	}
	
	public function index()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
				
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
				}
				else
				{
					$kode_kantor = "";
				}
				
				if((!empty($_GET['isAktif'])) && ($_GET['isAktif']!= "")  )
				{
					$isAktif = str_replace("'","",$_GET['isAktif']);
				}
				else
				{
					$isAktif = "";
				}
				
				if((!empty($_GET['masa_kerja'])) && ($_GET['masa_kerja']!= "")  )
				{
					$masa_kerja = str_replace("'","",$_GET['masa_kerja']);
				}
				else
				{
					$masa_kerja = "";
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					//AND (A.isAktif = 'DITERIMA' OR A.isAktif = '' OR A.isAktif = '0'  OR A.isAktif = 'PHK' OR A.isAktif = 'RESIGN' OR A.isAktif = 'MUTASI')
					
					$cari = "WHERE A.kode_kantor = '".$kode_kantor."' 
							AND 
								(
									A.no_karyawan LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR A.nik_karyawan LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR A.nama_karyawan LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)
							AND
							CASE 
								WHEN '".$isAktif."' = '' THEN
									A.isAktif NOT IN ('RESIGN','MUTASI','PHK')
								ELSE
									A.isAktif = '".$isAktif."'
								END
							AND
							CASE 
								WHEN '".$masa_kerja."' = '' THEN
									A.MASA_KERJA >= 0
								WHEN '".$masa_kerja."' = '1' THEN
									( (A.MASA_KERJA > 0) AND (A.MASA_KERJA <= 2) )
								WHEN '".$masa_kerja."' = '2' THEN
									( (A.MASA_KERJA > 2) AND (A.MASA_KERJA <= 3) )
								WHEN '".$masa_kerja."' = '3' THEN
									( (A.MASA_KERJA > 3) AND (A.MASA_KERJA <= 4) )
								WHEN '".$masa_kerja."' = '4' THEN
									( (A.MASA_KERJA > 4) AND (A.MASA_KERJA <= 5) )
								WHEN '".$masa_kerja."' = '5' THEN
									( (A.MASA_KERJA > 5) )
								ELSE
									A.MASA_KERJA >= 0
								END
							";
				}
				else
				{
					//AND (A.isAktif = 'DITERIMA' OR A.isAktif = '' OR A.isAktif = '0'  OR A.isAktif = 'PHK' OR A.isAktif = 'RESIGN' OR A.isAktif = 'MUTASI')
					$cari = "WHERE A.kode_kantor = '".$kode_kantor."' 
							AND
							CASE 
								WHEN '".$isAktif."' = '' THEN
									A.isAktif NOT IN ('RESIGN','MUTASI','PHK')
								ELSE
									A.isAktif = '".$isAktif."'
								END
							AND
							CASE 
								WHEN '".$masa_kerja."' = '' THEN
									A.MASA_KERJA >= 0
								WHEN '".$masa_kerja."' = '1' THEN
									( (A.MASA_KERJA > 0) AND (A.MASA_KERJA < 2) )
								WHEN '".$masa_kerja."' = '2' THEN
									( (A.MASA_KERJA >= 2) AND (A.MASA_KERJA < 3) )
								WHEN '".$masa_kerja."' = '3' THEN
									( (A.MASA_KERJA >= 3) AND (A.MASA_KERJA < 4) )
								WHEN '".$masa_kerja."' = '4' THEN
									( (A.MASA_KERJA >= 4) AND (A.MASA_KERJA < 5) )
								WHEN '".$masa_kerja."' = '5' THEN
									( (A.MASA_KERJA > 5) )
								ELSE
									A.MASA_KERJA >= 0
								END
							";
				}
				
				
				$list_karyawan = $this->M_gl_pst_karyawan->list_karyawan_limit($cari,100000,0);
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("WHERE CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN isViewClient IN (0,1) ELSE isViewClient = 0 END");
				
				$msgbox_title = " Data Karyawan ";
				
				
				$data = array('page_content'=>'gl_pusat_data_karyawan','msgbox_title' => $msgbox_title, 'list_karyawan' => $list_karyawan,'list_kantor' => $list_kantor,'kode_kantor' => $kode_kantor);
				$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-pusat-login-view');
			}
		}
	}

	function rekap_absensi()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
				if((!empty($_GET['periode'])) && ($_GET['periode']!= ""))
				{
					$periode = $_GET['periode'];
					$tgl_cur = date_create($periode.'-01');
			        $tgl_to = $tgl_cur->format('Y-m-23');

			        $tgl_conv = date_create($periode.'-01'.' first day of last month');
			        $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02
			        $kode_kantor = $_GET['kode_kantor'];
			        $cari = $_GET['cari'];
				} else {
					$tgl_from = '';
					$tgl_to = '';
					$kode_kantor = '';
					$cari = '';
				}


				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("");
				$list_rekap_absensi = $this->M_gl_pst_karyawan->list_rekap_absensi($tgl_from,$tgl_to,$kode_kantor,$cari);


				$data = array('page_content'=>'gl_pusat_rekap_absensi',
							  'msgbox_title' => 'Rekap Absensi', 
							  'list_kantor' => $list_kantor,
							  'list_rekap_absensi' => $list_rekap_absensi,
							  );
				$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-pusat-login-view');
			}
		}
	}
	
	function detail()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
				$id_karyawan = $this->uri->segment(2,0);
				$cari = "WHERE A.kode_kantor = '".$_GET['kode_kantor']."' AND MD5(A.id_karyawan) = '".$id_karyawan."'";
				$data_karyawan = $this->M_gl_karyawan->list_karyawan_limit($cari,1,0);
				if(!empty($data_karyawan))
				{
					//ROW DATA KARYAWAN
					$data_karyawan = $data_karyawan->row();
					
					if((!empty($_GET['dari'])) && ($_GET['dari']!= "")  )
					{
						$dari = $_GET['dari'];
						$sampai = $_GET['sampai'];
					}
					else
					{
						$dari = date("Y-m-d");
						$sampai = date("Y-m-d");
					}
					
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = "
								WHERE A.kode_kantor = '".$_GET['kode_kantor']."' 
								AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
								AND COALESCE(C.isProduk,'') IN ('PRODUK','JASA')
								AND DATE(COALESCE(B.tgl_h_penjualan,NOW())) BETWEEN DATE('".$dari."') AND DATE('".$sampai."')
								AND COALESCE(F.NOMINAL,0) > 0
								
								AND (
										COALESCE(B.id_dokter,'') = '".$data_karyawan->id_karyawan."'
										OR COALESCE(B.id_dokter2,'') = '".$data_karyawan->id_karyawan."'
										OR COALESCE(B.id_ass_dok,'') = '".$data_karyawan->id_karyawan."'
										OR COALESCE(B.id_ass_dok2,'') = '".$data_karyawan->id_karyawan."'
									)
									
								AND 
								(
									COALESCE(B.no_faktur,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR COALESCE(B.no_costmer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(B.nama_costumer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(D.nama_karyawan,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(E.nama_karyawan,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(C.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(C.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)";
					}
					else
					{
						$cari = "
								WHERE A.kode_kantor = '".$_GET['kode_kantor']."' 
								AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
								AND COALESCE(C.isProduk,'') IN ('PRODUK','JASA')
								AND DATE(COALESCE(B.tgl_h_penjualan,NOW())) BETWEEN DATE('".$dari."') AND DATE('".$sampai."')
								AND COALESCE(F.NOMINAL,0) > 0
								AND (
										COALESCE(B.id_dokter,'') = '".$data_karyawan->id_karyawan."'
										OR COALESCE(B.id_dokter2,'') = '".$data_karyawan->id_karyawan."'
										OR COALESCE(B.id_ass_dok,'') = '".$data_karyawan->id_karyawan."'
										OR COALESCE(B.id_ass_dok2,'') = '".$data_karyawan->id_karyawan."'
									)
							";
					}
					$order_by = "ORDER BY A.id_h_penjualan DESC, COALESCE(C.isProduk,'') ASC";
					
					
					$list_apoteker = $this->M_gl_lap_apoteker->list_apoteker_today($cari,$order_by);
					
					
					
					$list_data_kantor = $this->M_gl_karyawan->get_data_kantor(" WHERE kode_kantor <> '".$this->session->userdata('ses_kode_kantor')."'");
					
					$msgbox_title = " Detail Karyawan ".$data_karyawan->nama_karyawan;
					
					
					
					$data = array('page_content'=>'gl_pusat_data_karyawan_detail','data_karyawan' => $data_karyawan,'msgbox_title' => $msgbox_title,'list_apoteker' => $list_apoteker,'list_data_kantor' => $list_data_kantor,'kode_kantor' => $_GET['kode_kantor']);
					$this->load->view('pusat/container',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-pusat-data-karyawan?kode_kantor='.$_GET['kode_kantor']);
				}
			}
			else
			{
				header('Location: '.base_url().'gl-pusat-login-view');
			}
		}
	}
	
	function list_keluarga()
	{
		$cari = " WHERE id_karyawan = '".$_POST['id_karyawan']."' AND kode_kantor = '".$_POST['kode_kantor']."' ";
		
		
		
		$list_keluarga = $this->M_gl_keluarga->list_keluarga_limit($cari,20,0);
		
		if(!empty($list_keluarga))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="example2" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="35%">BIODATA</th>';
							echo '<th width="60%">KETERANGAN</th>';
							
				echo '</tr>
</thead>';
				$list_result = $list_keluarga->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					echo'<tr id="tr_'.$no.'">';
					
						echo'<td>'.$no.'</td>';
						
						echo'<td>
								<b>HUBUNGAN : </b>'.$row->nama_hub .' 
								<br/> <b>NIK : </b>'.$row->nik_kel.' 
								<br/> <b>NAMA : </b>'.$row->nama.'
								<br/> <b>TTL : </b>'.$row->tempat_lahir.','.$row->tgl_lahir.' ('.$row->USIA.')
								<br/> <b>PENDIDIKAN : </b>'.$row->pnd.'
							</td>';
						echo'<td>
								<b>TELPON : </b>'.$row->tlp .' 
								<br/> <b>EMAIL : </b>'.$row->email.'
								<br/> <b>HIRARKI : </b>'.$row->hirarki.'
								<br/> <b>KELAMIN : </b>'.$row->kelamin.'
								<br/> <b>ALAMAT : </b>'.$row->alamat.' 
								<br/> <b>KETERANGAN : </b>'.$row->nama.'
							</td>';
						// echo'<td>
								// <button type="button" onclick="insert('.$no.')" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#myModal">Pilih</button>
							// </td>';
						/*
						echo'<input type="hidden" id="id_karyawan_3_'.$no.'" name="id_karyawan_3_'.$no.'" value="'.$row->id_karyawan.'" />';
						echo'<input type="hidden" id="no_karyawan_3_'.$no.'" name="no_karyawan_3_'.$no.'" value="'.$row->no_karyawan.'" />';
						echo'<input type="hidden" id="nama_karyawan_3_'.$no.'" name="nama_karyawan_3_'.$no.'" value="'.$row->nama_karyawan.'" />';
						*/
						
						echo'<input type="hidden" id="id_kel_'.$no.'" name="id_kel_'.$no.'" value="'.$row->id_kel.'" />';
						echo'<input type="hidden" id="id_karyawan_'.$no.'" name="id_karyawan_'.$no.'" value="'.$row->id_karyawan.'" />';
						echo'<input type="hidden" id="nama_hub_'.$no.'" name="nama_hub_'.$no.'" value="'.$row->nama_hub.'" />';
						echo'<input type="hidden" id="nik_kel_'.$no.'" name="nik_kel_'.$no.'" value="'.$row->nik_kel.'" />';
						echo'<input type="hidden" id="nama_'.$no.'" name="nama_'.$no.'" value="'.$row->nama.'" />';
						echo'<input type="hidden" id="tempat_lahir_'.$no.'" name="tempat_lahir_'.$no.'" value="'.$row->tempat_lahir.'" />';
						echo'<input type="hidden" id="tgl_lahir_'.$no.'" name="tgl_lahir_'.$no.'" value="'.$row->tgl_lahir.'" />';
						echo'<input type="hidden" id="kelamin_'.$no.'" name="kelamin_'.$no.'" value="'.$row->kelamin.'" />';
						echo'<input type="hidden" id="tlp_'.$no.'" name="tlp_'.$no.'" value="'.$row->tlp.'" />';
						echo'<input type="hidden" id="email_'.$no.'" name="email_'.$no.'" value="'.$row->email.'" />';
						echo'<input type="hidden" id="pnd_'.$no.'" name="pnd_'.$no.'" value="'.$row->pnd.'" />';
						echo'<input type="hidden" id="alamat_'.$no.'" name="alamat_'.$no.'" value="'.$row->alamat.'" />';
						echo'<input type="hidden" id="ket_kel_'.$no.'" name="ket_kel_'.$no.'" value="'.$row->ket_kel.'" />';
						echo'<input type="hidden" id="hirarki_'.$no.'" name="hirarki_'.$no.'" value="'.$row->hirarki.'" />';
						echo'<input type="hidden" id="user_ins_'.$no.'" name="user_ins_'.$no.'" value="'.$row->user_ins.'" />';
						echo'<input type="hidden" id="user_updt_'.$no.'" name="user_updt_'.$no.'" value="'.$row->user_updt.'" />';
						echo'<input type="hidden" id="tgl_ins_'.$no.'" name="tgl_ins_'.$no.'" value="'.$row->tgl_ins.'" />';
						echo'<input type="hidden" id="tgl_updt_'.$no.'" name="tgl_updt_'.$no.'" value="'.$row->tgl_updt.'" />';
						echo'<input type="hidden" id="kode_kantor_'.$no.'" name="kode_kantor_'.$no.'" value="'.$row->kode_kantor.'" />';

						
						
					echo'</tr>';
					$no++;
				}
				
				echo '</tbody>';
			echo'</table>';
			echo'</div>';
		}
		else
		{
			echo "TIDAK ADA DATA YANG DITAMPILKAN";
		}
	}


	function list_sekolah()
	{
		/*
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (COALESCE(A.no_karyawan,'') LIKE '%".$_POST['cari']."%' OR COALESCE(A.nama_karyawan,'') LIKE '%".$_POST['cari']."%')
							";
		}
		else
		{
			$cari = "AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
		}
		*/
		
		$cari = " WHERE id_karyawan = '".$_POST['id_karyawan']."' AND kode_kantor = '".$_POST['kode_kantor']."' ";
		
		
		
		$list_sekolah = $this->M_gl_sekolah->list_sekolah_limit($cari,20,0);
		
		if(!empty($list_sekolah))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="example2" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="40%">BIODATA</th>';
							echo '<th width="55%">KETERANGAN</th>';
							
				echo '</tr>
</thead>';
				$list_result = $list_sekolah->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					if($row->tingkat != 'NON FORMAL')
					{
						echo'<tr id="tr_'.$no.'" style="background-color:#90EE90;">';
					}
					else
					{
						echo'<tr id="tr_'.$no.'">';
					}
					
					
						echo'<td>'.$no.'</td>';
						
						echo'<td>
								<b>TINGKAT : </b>'.$row->tingkat .' 
								<br/> <b>NAMA : </b>'.$row->nama_sekolah.'
								<br/> <b>NILAI : </b>'.$row->nilai.'
								<br/> <b>TAHUN LULUS : </b>'.$row->tahun_lulus.'
							</td>';
						echo'<td>
								<b>HIRARKI : </b>'.$row->hirarki .'
								<br/> <b>ALAMAT : </b>'.$row->alamat .'
								<br/> <b>KETERANGAN : </b>'.$row->ket_sekolah .'
							</td>';
							
							
						echo'<input type="hidden" id="id_sekolah_2_'.$no.'" name="id_sekolah_2_'.$no.'" value="'.$row->id_sekolah.'" />';
						echo'<input type="hidden" id="id_karyawan_2_'.$no.'" name="id_karyawan_2_'.$no.'" value="'.$row->id_karyawan.'" />';
						echo'<input type="hidden" id="tingkat_2_'.$no.'" name="tingkat_2_'.$no.'" value="'.$row->tingkat.'" />';
						echo'<input type="hidden" id="nama_sekolah_2_'.$no.'" name="nama_sekolah_2_'.$no.'" value="'.$row->nama_sekolah.'" />';
						echo'<input type="hidden" id="nilai_2_'.$no.'" name="nilai_2_'.$no.'" value="'.$row->nilai.'" />';
						echo'<input type="hidden" id="tahun_lulus_2_'.$no.'" name="tahun_lulus_2_'.$no.'" value="'.$row->tahun_lulus.'" />';
						echo'<input type="hidden" id="alamat_2_'.$no.'" name="alamat_2_'.$no.'" value="'.$row->alamat.'" />';
						echo'<input type="hidden" id="ket_sekolah_2_'.$no.'" name="ket_sekolah_2_'.$no.'" value="'.$row->ket_sekolah.'" />';
						echo'<input type="hidden" id="hirarki_2_'.$no.'" name="hirarki_2_'.$no.'" value="'.$row->hirarki.'" />';
						echo'<input type="hidden" id="user_ins_2_'.$no.'" name="user_ins_2_'.$no.'" value="'.$row->user_ins.'" />';
						echo'<input type="hidden" id="user_updt_2_'.$no.'" name="user_updt_2_'.$no.'" value="'.$row->user_updt.'" />';
						echo'<input type="hidden" id="tgl_ins_2_'.$no.'" name="tgl_ins_2_'.$no.'" value="'.$row->tgl_ins.'" />';
						echo'<input type="hidden" id="tgl_updt_2_'.$no.'" name="tgl_updt_2_'.$no.'" value="'.$row->tgl_updt.'" />';
						echo'<input type="hidden" id="kode_kantor_2_'.$no.'" name="kode_kantor_2_'.$no.'" value="'.$row->kode_kantor.'" />';
						
					echo'</tr>';
					$no++;
				}
				
				echo '</tbody>';
			echo'</table>';
			echo'</div>';
		}
		else
		{
			echo "TIDAK ADA DATA YANG DITAMPILKAN";
		}
	}


	function list_training()
	{
		$cari = " WHERE A.id_karyawan = '".$_POST['id_karyawan']."' AND A.kode_kantor = '".$_POST['kode_kantor']."' ";
		
		
		
		$list_training = $this->M_gl_karyawan_training->get_list_karyawan_training($cari,30,0);
		//($cari,20,0);
		
		if(!empty($list_training))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="example2" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="55%">EVENT</th>';
							echo '<th width="40%">HASIL</th>';
				echo '</tr>
</thead>';
				$list_result = $list_training->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					echo'<tr id="tr_'.$no.'">';
						echo'<td>'.$no.'</td>';
						
						echo'<td>
								<b>NAMA : </b>'.$row->nama_event .' 
								<br/> <b>PANITIA : </b>'.$row->panitia.'
								<br/> <b>TAHUN : </b>'.$row->tahun.'
								<br/> <b>ALAMAT : </b>'.$row->alamat.'
							</td>';
						echo'<td>
								<b>NILAI : </b>'.$row->nilai .'
								<br/> <b>KETERANGAN : </b>'.$row->ket_karyawan_training .'
							</td>';
					echo'</tr>';
					$no++;
				}
				
				echo '</tbody>';
			echo'</table>';
			echo'</div>';
		}
		else
		{
			echo "TIDAK ADA DATA YANG DITAMPILKAN";
		}
	}


	function list_riwayat_jabatan()
	{
		$list_riwayat_jabatan = $this->M_gl_karyawan_jabatan->list_riwayat_jabatan_karyawan($_POST['kode_kantor'],$_POST['id_karyawan']);
		//($cari,20,0);
		
		if(!empty($list_riwayat_jabatan))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="example2" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="27%">JABATAN</th>';
							echo '<th width="43%">KETERANGAN</th>';
							echo '<th width="25%">HASIL</th>';
				echo '</tr>
</thead>';
				$list_result = $list_riwayat_jabatan->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					echo'<tr id="tr_'.$no.'">';
						echo'<td>'.$no.'</td>';
						
						echo'<td>
								<b>TYPE : </b>'.$row->tipe.'
								<br/> <b>DEPT : </b>'.$row->nama_dept .' ('.$row->nama_dept.') 
								<br/> <b>JABATAN : </b>'.$row->nama_jabatan.' ('.$row->kode_jabatan.')
							</td>';
						echo'<td>
								<b>KODE : </b>'.$row->kode_promosi .'
								<br/> <b>SURAT SK : </b>'.$row->no_surat .'
								<br/> <b>PERIODE : </b>'.$row->rekomendasi .'
								<br/> <b>MASA PERCOBAAN : </b>'.$row->tgl_dari .' - '.$row->Akhir_percobaan.'
								<br/> <b>KETERANGAN : </b>'.$row->keterangan .'
							</td>';
						echo'<td>
								<b>STATUS : </b>'.$row->status .'
								<br/> <b>NILAI : </b>'.$row->nilai .'
								<br/> <b>CATATAN : </b>'.$row->ket_nilai .'
							</td>';
					echo'</tr>';
					$no++;
				}
				
				echo '</tbody>';
			echo'</table>';
			echo'</div>';
		}
		else
		{
			echo "TIDAK ADA DATA YANG DITAMPILKAN";
		}
	}

	function list_riwayat_punishment()
	{
		$cari = " WHERE A.id_karyawan = '".$_POST['id_karyawan']."' AND A.kode_kantor = '".$_POST['kode_kantor']."' ";
		$list_riwayat_punishment = $this->M_gl_punish->list_punish_limit($cari,30,0);
		//($cari,20,0);
		
		if(!empty($list_riwayat_punishment))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="example2" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="40%">PERATURRAN</th>';
							echo '<th width="55%">PUNISHMENT</th>';
				echo '</tr>
</thead>';
				$list_result = $list_riwayat_punishment->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					echo'<tr id="tr_'.$no.'">';
						echo'<td>'.$no.'</td>';
						
						echo'<td>
								<b>KODE : </b>'.$row->kode_per.'
								<br/> <b>NAMA : </b>'.$row->nama_per .' 
								<br/> <b>KETERANGAN : </b>'.$row->ket_per.'
							</td>';
						echo'<td>
								<b>NO : </b>'.$row->no_pelanggaran .'
								<br/> <b>JUDUL : </b>'.$row->nama_pelanggaran .'
								<br/> <b>HUKUMAN : </b>'.$row->hukuman .'
								<br/> <b>MASA PERCOBAAN : </b>'.$row->tgl_mulai .' - '.$row->tgl_selesai.'
								<br/> <b>KRONOLOGI : </b>'.$row->kronologi .'
							</td>';
						
					echo'</tr>';
					$no++;
				}
				
				echo '</tbody>';
			echo'</table>';
			echo'</div>';
		}
		else
		{
			echo "TIDAK ADA DATA YANG DITAMPILKAN";
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_gl_pst_karyawan.php */