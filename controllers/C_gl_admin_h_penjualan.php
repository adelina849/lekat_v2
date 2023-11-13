<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_h_penjualan extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_h_penjualan','M_gl_costumer','M_gl_karyawan','M_gl_images','M_gl_h_diskon'));
		
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
				if(!empty($this->uri->segment(2,0)))
				{
					$id_h_penjualan = ($this->uri->segment(2,0));
					
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
								AND MD5(A.id_h_penjualan) = '".$id_h_penjualan."'
								AND A.sts_penjualan = 'PENDING'
								AND (A.no_faktur LIKE '%".str_replace("'","",$_GET['cari'])."%' OR COALESCE(B.nama_lengkap,A.nama_costumer) LIKE '%".str_replace("'","",$_GET['cari'])."%')";
					}
					else
					{
						$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(A.id_h_penjualan) = '".$id_h_penjualan."' AND A.sts_penjualan = 'PENDING'";
					}
					
					$order_by = "ORDER BY A.tgl_ins DESC";
					$data_h_penjualan = $this->M_gl_h_penjualan->list_pendaftaran($cari,$order_by,1,0);
					if(!empty($data_h_penjualan))
					{
						$data_h_penjualan = $data_h_penjualan->row();
						
						/*DATA COSTUMER*/
							$cari_costumer = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_costumer = '".$data_h_penjualan->id_costumer."'";
							$data_costumer = $this->M_gl_costumer->list_costumer_biasa($cari_costumer,1,0);
							if(!empty($data_costumer))
							{
								$data_costumer = $data_costumer->row();
							}
							else
							{
								$data_costumer = false;
							}
							
							$id_dokter = $data_h_penjualan->id_dokter;
							$data_dokter = $this->M_gl_karyawan->get_karyawan('id_karyawan',$id_dokter);
							if(!empty($data_dokter))
							{
								$data_dokter = $data_dokter->row();
							}
							else
							{
								$data_dokter = false;
							}
						/*DATA COSTUMER*/
						
						/*DATA KANTOR*/
							// $cari_kantor = " WHERE kode_kantor <> '".$this->session->userdata('ses_kode_kantor')."'";
							$list_kantor = $this->M_gl_pengaturan->get_data_kantor("");
						/*DATA KANTOR*/
						
						/*TAMPILKAN*/
						$msgbox_title = " Pendaftaran Pasien ";
					
						$data = array('page_content'=>'gl_admin_h_penjualan_daftar','msgbox_title' => $msgbox_title,'data_h_penjualan' => $data_h_penjualan,'data_costumer' => $data_costumer,'data_dokter' => $data_dokter,'list_kantor' => $list_kantor);
						$this->load->view('admin/container',$data);
						/*TAMPILKAN*/

					}
					else
					{
						header('Location: '.base_url().'gl-admin-pendaftaran-pasien');
					}
				}
				else
				{
					$data_h_penjualan = false;
					$data_costumer = false;
					
					/*DATA KANTOR*/
						// $cari_kantor = " WHERE kode_kantor <> '".$this->session->userdata('ses_kode_kantor')."'";
						$list_kantor = $this->M_gl_pengaturan->get_data_kantor("");
					/*DATA KANTOR*/
					
					/*TAMPILKAN*/
					$msgbox_title = " Pendaftaran Pasien ";
				
					$data = array('page_content'=>'gl_admin_h_penjualan_daftar','msgbox_title' => $msgbox_title,'data_h_penjualan' => $data_h_penjualan,'data_costumer' => $data_costumer,'list_kantor' => $list_kantor);
					$this->load->view('admin/container',$data);
					/*TAMPILKAN*/
				}
				
				
				
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function list_h_diskon_paket()
	{	
		
		$list_h_diskon_paket = $this->M_gl_h_diskon->list_h_diskon_paket($this->session->userdata('ses_kode_kantor'),$_POST['id_kat_costumer']);
		
		echo'<option value=""></option>';
		if(!empty($list_h_diskon_paket))
		{
				$list_result = $list_h_diskon_paket->result();
				foreach($list_result as $row)
				{
					echo'<option value="'.$row->id_h_diskon.'">'.$row->nama_diskon.'</option>';
				}
				
		}
		else
		{
			
		}
	}
	
	function list_pasien()
	{	
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			/*
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND (A.no_costumer LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.nama_lengkap LIKE '%".str_replace("'","",$_POST['cari'])."%' )";
			*/
			$cari = "
					WHERE A.kode_kantor = '".str_replace("'","",$_POST['kode_kantor'])."' AND (A.no_costumer LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.nama_lengkap LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.hp LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.hp_pnd LIKE '%".str_replace("'","",$_POST['cari'])."%' )
			";
		}
		else
		{
			//$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
			$cari = "WHERE A.kode_kantor = '".str_replace("'","",$_POST['kode_kantor'])."'";
		}
	
		
		$list_costumer = $this->M_gl_costumer->list_costumer_biasa($cari,$_POST['limit'],$_POST['offset']);
		//-- list_supplier_limit($cari,$_POST['limit'],$_POST['offset']);
		
		if(!empty($list_costumer))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="table_list_produk" class="table table-hover">';
				echo '<thead>
<tr>';
						if($this->session->userdata('ses_isModePst') == "YA")
						{
							echo '<th width="5%">NO</th>';
							echo '<th width="15%">FOTO</th>';
							echo '<th width="65%">CUSTOMER/CABANG</th>';
							echo '<th width="15%">PILIH</th>';
						}
						else
						{
							echo '<th width="5%">NO</th>';
							echo '<th width="15%">FOTO</th>';
							echo '<th width="65%">BIODATA</th>';
							echo '<th width="15%">PILIH</th>';
						}
							
				echo '</tr>
</thead>';
				$list_result = $list_costumer->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					//echo'<tr id="tr_'.$no.'">';
					echo'<tr id="tr_list_costumer-'.$no.'">';
					echo'<td>'.$no.'</td>';
					if($row->avatar == "")
					{
						$src = base_url().'assets/global/costumer/loading.gif';
						echo '<td><img id="IMG_'.$no.'"  width="100%" style="border:1px solid #C8C8C8; padding:5px; float:left;" src="'.$src.'" />
						</td>';
					}
					else
					{
						//$src = base_url().'assets/global/costumer/'.$row->avatar;
						$src = base_url().''.$row->avatar_url.''.$row->avatar;
						echo '<td><img id="IMG_'.$no.'"  width="100%" style="border:1px solid #C8C8C8; padding:5px; float:left;" src="'.$src.'" />
						</td>';
					}
					
					if($this->session->userdata('ses_isModePst') == "YA")
					{
						echo'<td>
								<b>NO CUSTOMER : </b>'.$row->no_costumer.' 
								<br/> <b>NAMA : </b>'.$row->nama_lengkap.' 
								<br/> <b>MEMBER : </b>'.$row->nama_kat_costumer.' 
								<br/> <b>No Tlp : </b>'.$row->hp.' / '.$row->hp_pnd.' 
								<br/> <div style="color:red;"><b>CABANG : </b>'.$row->kode_kantor.'</div>
							</td>';
					}
					else
					{
						echo'<td>
								<b>NO PASIEN : </b>'.$row->no_costumer.' 
								<br/> <b>NAMA : </b>'.$row->nama_lengkap.' 
								<br/> <b>MEMBER : </b>'.$row->nama_kat_costumer.' 
								<br/> <b>No Tlp : </b>'.$row->hp.' / '.$row->hp_pnd.' 
								<br/> <div style="color:red;"><b>CABANG : </b>'.$row->kode_kantor.'</div>
							</td>';
					}
					
					
					if($_POST['from'] == 'transaksi')
					{
						echo'<td>
							<button type="button" onclick="ubah_pasien('.$no.')" class="btn btn-primary btn-sm" data-dismiss="modal">Pilih</button>
						</td>';
					}
					else
					{
						
						echo'<td>
							<button type="button" onclick="insert_pasien('.$no.')" class="btn btn-primary btn-sm" data-dismiss="modal">Pilih</button>
						</td>';
					}
						
					echo'<input type="hidden" id="get_tr_2_'.$no.'" name="get_tr_2_'.$no.'" value="tr_list_pasien-'.$no.'" />';
						
	echo'<input type="hidden" id="url_fix_'.$no.'" name="url_fix_'.$no.'" value="'.$src.'" />';
	echo'<input type="hidden" id="id_costumer_2_'.$no.'" name="id_costumer_2_'.$no.'" value="'.$row->id_costumer.'" />';
	echo'<input type="hidden" id="nik_2_'.$no.'" name="nik_2_'.$no.'" value="'.$row->nik.'" />';
	echo'<input type="hidden" id="id_kat_costumer_2_'.$no.'" name="id_kat_costumer_2_'.$no.'" value="'.$row->id_kat_costumer.'" />';
	echo'<input type="hidden" id="nama_kat_costumer_2_'.$no.'" name="nama_kat_costumer_2_'.$no.'" value="'.$row->nama_kat_costumer.'" />';
	echo'<input type="hidden" id="no_costumer_2_'.$no.'" name="no_costumer_2_'.$no.'" value="'.$row->no_costumer.'" />';
	echo'<input type="hidden" id="nama_lengkap_2_'.$no.'" name="nama_lengkap_2_'.$no.'" value="'.$row->nama_lengkap.'" />';
	echo'<input type="hidden" id="jenis_kelamin_2_'.$no.'" name="jenis_kelamin_2_'.$no.'" value="'.$row->jenis_kelamin.'" />';
	echo'<input type="hidden" id="pendidikan_2_'.$no.'" name="pendidikan_2_'.$no.'" value="'.$row->pendidikan.'" />';
	echo'<input type="hidden" id="pekerjaan_2_'.$no.'" name="pekerjaan_2_'.$no.'" value="'.$row->pekerjaan.'" />';
	echo'<input type="hidden" id="hp_2_'.$no.'" name="hp_2_'.$no.'" value="'.$row->hp.'" />';
	echo'<input type="hidden" id="email_costumer_2_'.$no.'" name="email_costumer_2_'.$no.'" value="'.$row->email_costumer.'" />';
	echo'<input type="hidden" id="tgl_registrasi_2_'.$no.'" name="tgl_registrasi_2_'.$no.'" value="'.$row->tgl_registrasi.'" />';
	echo'<input type="hidden" id="alamat_rumah_sekarang_2_'.$no.'" name="alamat_rumah_sekarang_2_'.$no.'" value="'.$row->alamat_rumah_sekarang.'" />';
						
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
	
	function ubah_costumer_saat_transaksi()
	{
		$query = "UPDATE tb_h_penjualan SET id_costumer = '".$_POST['id_costumer']."',no_costmer = '".$_POST['no_costumer']."', nama_costumer = '".$_POST['nama_costumer']."' WHERE kode_kantor = '".$_POST['kode_kantor']."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."'";
		$this->M_gl_pengaturan->exec_query_general($query);
		echo'BERHASIL';
	}
	
	function scan_pasien()
	{	
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND A.no_costumer LIKE '%".str_replace("'","",$_POST['cari'])."%' ";
		}
		else
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
		}
	
		
		$data_costumer = $this->M_gl_costumer->list_costumer_biasa($cari,$_POST['limit'],$_POST['offset']);
		//-- list_supplier_limit($cari,$_POST['limit'],$_POST['offset']);
		
		if(!empty($data_costumer))
		{
			$data_costumer = $data_costumer->row();
			if($data_costumer->avatar == "")
			{
				$src = base_url().'assets/global/costumer/loading.gif';
			}
			else
			{
				$src = base_url().''.$data_costumer->avatar_url.''.$data_costumer->avatar;
			}
			
			echo  $src.'|'.$data_costumer->id_costumer.'|'.$data_costumer->nik.'|'.$data_costumer->nama_kat_costumer.'|'.$data_costumer->no_costumer.'|'.$data_costumer->nama_lengkap.'|'.$data_costumer->jenis_kelamin.'|'.$data_costumer->pendidikan.'|'.$data_costumer->pekerjaan.'|'.$data_costumer->hp.'|'.$data_costumer->email_costumer.'|'.$data_costumer->tgl_registrasi.'|'.$data_costumer->alamat_rumah_sekarang.'|'.$data_costumer->id_kat_costumer;
		}
		else
		{
			//echo "KOSONG";
			return false;
		}
	}
	
	function get_no_antrian()
	{
		if($_POST['stat_edit'] == "")
		{
			$data_no_antrian = $this->M_gl_h_penjualan->get_no_antrian($this->session->userdata('ses_kode_kantor'));
			if(!empty($data_no_antrian))
			{
				$data_no_antrian = $data_no_antrian->row();
				$this->M_gl_h_penjualan->simpan_h_penjualan_awal
				(
					$data_no_antrian->id_h_penjualan,
					$_POST['id_costumer'],
					$this->session->userdata('ses_id_karyawan'), //$_POST['id_karyawan'],
					$_POST['id_dokter'], //id_dokter
					$_POST['id_h_diskon'], //id_h_diskon
					$data_no_antrian->no_faktur,
					$data_no_antrian->no_antrian,
					$_POST['no_costmer'],
					$_POST['nama_costumer'],
					$_POST['nama_kat_costumer'],
					$_POST['id_kat_costumer'],
					$_POST['kode_kantor_costumer'],
					$_POST['tgl_h_penjualan'],
					'CASH',
					$_POST['ket_penjualan'],
					$_POST['type_h_penjualan'],
					$this->session->userdata('ses_id_karyawan'),
					$this->session->userdata('ses_kode_kantor'),
					'PENDING', //$_POST['sts_penjualan']
					'' //MEDIA TRANSAKSI
				);
				echo $data_no_antrian->id_h_penjualan.'|'.$data_no_antrian->no_faktur.'|'.$data_no_antrian->no_antrian;
			}
		}
		else
		{
			$this->M_gl_h_penjualan->edit_h_penjualan_awal
			(
				$_POST['stat_edit'],
				$_POST['id_costumer'],
				$this->session->userdata('ses_id_karyawan'), //$_POST['id_karyawan'],
				$_POST['id_dokter'], //id_dokter
				$_POST['id_h_diskon'], //id_h_diskon
				$_POST['no_costmer'],
				$_POST['nama_costumer'],
				$_POST['nama_kat_costumer'],
				$_POST['id_kat_costumer'],
				$_POST['kode_kantor_costumer'],
				$_POST['tgl_h_penjualan'],
				'CASH',
				$_POST['ket_penjualan'],
				$_POST['type_h_penjualan'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor'),
				'PENDING' //$_POST['sts_penjualan']
			);
			
			/* CATAT AKTIFITAS EDIT*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'UPDATE',
					'Melakukan Penyelesaian Pendaftaran Pasien '.$_POST['nama_costumer'].' Kunjungan Tanggal '.$_POST['tgl_h_penjualan'],
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS EDIT*/
			
			echo $_POST['stat_edit'];
			//echo "BERHASIL";
		}
		//echo $data_no_antrian->id_h_penjualan.'|'.$data_no_antrian->no_faktur.'|'.$data_no_antrian->no_antrian;
	}

	function list_dokter()
	{	
		
		
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND (A.no_karyawan LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.nik_karyawan LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.nama_karyawan LIKE '%".str_replace("'","",$_POST['cari'])."%')
					AND (A.isAktif = 'DITERIMA' OR A.isAktif = '' OR A.isAktif = '0'  OR A.isAktif = 'PHK' OR A.isAktif = 'RESIGN') AND A.isDokter ='DOKTER'";
		}
		else
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND (A.isAktif = 'DITERIMA' OR A.isAktif = '' OR A.isAktif = '0'  OR A.isAktif = 'PHK' OR A.isAktif = 'RESIGN') AND A.isDokter ='DOKTER'";
		}
	
		
		$list_dokter = $this->M_gl_karyawan->list_karyawan_limit($cari,$_POST['limit'],$_POST['offset']);
		//-- list_supplier_limit($cari,$_POST['limit'],$_POST['offset']);
		
		if(!empty($list_dokter))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="table_list_produk" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="15%">FOTO</th>';
							echo '<th width="65%">BIODATA</th>';
							echo '<th width="15%">PILIH</th>';
				echo '</tr>
</thead>';
				$list_result = $list_dokter->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					//echo'<tr id="tr_'.$no.'">';
					echo'<tr id="tr_list_costumer-'.$no.'">';
					echo'<td>'.$no.'</td>';
					if($row->avatar == "")
					{
						$src = base_url().'assets/global/costumer/loading.gif';
						echo '<td><img id="IMG_'.$no.'"  width="100%" style="border:1px solid #C8C8C8; padding:5px; float:left;" src="'.$src.'" />
						</td>';
					}
					else
					{
						//$src = base_url().'assets/global/costumer/'.$row->avatar;
						$src = base_url().''.$row->avatar_url.''.$row->avatar;
						echo '<td><img id="IMG_'.$no.'"  width="100%" style="border:1px solid #C8C8C8; padding:5px; float:left;" src="'.$src.'" />
						</td>';
					}
					
						echo'<td>
								<b>NO : </b>'.$row->no_karyawan.' 
								<br/> <b>NIK : </b>'.$row->nik_karyawan.' 
								<br/> <b>NAMA : </b>'.$row->nama_karyawan.'
								<br/> <b>PENDIDIKAN : </b>'.$row->pnd.' 
								<br/> <b>KELAMIN : </b>'.$row->kelamin.'
								<br/> <b>ALAMAT : </b>'.$row->alamat.'
								<!-- <br/> <b>LAMA KERJA : </b>'.$row->lama_kerja.' -->
							</td>';
						
						echo'<td>
								<button type="button" onclick="insert_dokter('.$no.')" class="btn btn-primary btn-sm" data-dismiss="modal">Pilih</button>
							</td>';
							
						echo'<input type="hidden" id="get_tr_2_'.$no.'" name="get_tr_2_'.$no.'" value="tr_list_pasien-'.$no.'" />';
						
	echo'<input type="hidden" id="url_fix_3_'.$no.'" name="url_fix_3_'.$no.'" value="'.$src.'" />';
	echo'<input type="hidden" id="no_3_'.$no.'" name="no_3_'.$no.'" value="'.$no.'" />';
	echo'<input type="hidden" id="id_karyawan_3_'.$no.'" name="id_karyawan_3_'.$no.'" value="'.$row->id_karyawan.'" />';
	echo'<input type="hidden" id="id_jabatan_3_'.$no.'" name="id_jabatan_3_'.$no.'" value="'.$row->id_jabatan.'" />';
	echo'<input type="hidden" id="no_karyawan_3_'.$no.'" name="no_karyawan_3_'.$no.'" value="'.$row->no_karyawan.'" />';
	echo'<input type="hidden" id="nik_karyawan_3_'.$no.'" name="nik_karyawan_3_'.$no.'" value="'.$row->nik_karyawan.'" />';
	echo'<input type="hidden" id="nama_karyawan_3_'.$no.'" name="nama_karyawan_3_'.$no.'" value="'.$row->nama_karyawan.'" />';
	echo'<input type="hidden" id="pnd_3_'.$no.'" name="pnd_3_'.$no.'" value="'.$row->pnd.'" />';
	echo'<input type="hidden" id="tlp_3_'.$no.'" name="tlp_3_'.$no.'" value="'.$row->tlp.'" />';
	echo'<input type="hidden" id="email_3_'.$no.'" name="email_3_'.$no.'" value="'.$row->email.'" />';
	
	echo'<input type="hidden" id="tmp_lahir_3_'.$no.'" name="tmp_lahir_3_'.$no.'" value="'.$row->tmp_lahir.'" />';
	echo'<input type="hidden" id="tgl_lahir_3_'.$no.'" name="tgl_lahir_3_'.$no.'" value="'.$row->tgl_lahir.'" />';
	echo'<input type="hidden" id="kelamin_3_'.$no.'" name="kelamin_3_'.$no.'" value="'.$row->kelamin.'" />';
	
	echo'<input type="hidden" id="avatar_3_'.$no.'" name="avatar_3_'.$no.'" value="'.$row->avatar.'" />';
	echo'<input type="hidden" id="avatar_url_3_'.$no.'" name="avatar_url_3_'.$no.'" value="'.$row->avatar_url.'" />';
	echo'<input type="hidden" id="alamat_3_'.$no.'" name="alamat_3_'.$no.'" value="'.$row->alamat.'" />';
	echo'<input type="hidden" id="ket_karyawan_3_'.$no.'" name="ket_karyawan_3_'.$no.'" value="'.$row->ket_karyawan.'" />';
	
	echo'<input type="hidden" id="lama_kerja_3_'.$no.'" name="lama_kerja_3_'.$no.'" value="'.$row->lama_kerja.'" />';
						
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
	
	function list_dokter_tersedia()
	{	
		
		
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND (A.no_karyawan LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.nik_karyawan LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.nama_karyawan LIKE '%".str_replace("'","",$_POST['cari'])."%')
					AND (A.isAktif = 'DITERIMA' OR A.isAktif = '' OR A.isAktif = '0'  OR A.isAktif = 'PHK' OR A.isAktif = 'RESIGN') AND A.isDokter ='DOKTER'
					-- AND B.id_h_penjualan IS NULL
					";
		}
		else
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND (A.isAktif = 'DITERIMA' OR A.isAktif = '' OR A.isAktif = '0'  OR A.isAktif = 'PHK' OR A.isAktif = 'RESIGN') AND A.isDokter ='DOKTER'
			-- AND B.id_h_penjualan IS NULL
			";
		}
	
		
		$list_dokter = $this->M_gl_karyawan->list_karyawan_yang_tersedia_dokter($cari,$_POST['limit'],$_POST['offset']);
		//-- list_supplier_limit($cari,$_POST['limit'],$_POST['offset']);
		
		if(!empty($list_dokter))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="table_list_produk" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="15%">FOTO</th>';
							echo '<th width="65%">BIODATA</th>';
							echo '<th width="15%">PILIH</th>';
				echo '</tr>
</thead>';
				$list_result = $list_dokter->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					//echo'<tr id="tr_'.$no.'">';
					echo'<tr id="tr_list_costumer-'.$no.'">';
					echo'<td>'.$no.'</td>';
					if($row->avatar == "")
					{
						$src = base_url().'assets/global/costumer/loading.gif';
						echo '<td><img id="IMG_'.$no.'"  width="100%" style="border:1px solid #C8C8C8; padding:5px; float:left;" src="'.$src.'" />
						</td>';
					}
					else
					{
						//$src = base_url().'assets/global/costumer/'.$row->avatar;
						$src = base_url().''.$row->avatar_url.''.$row->avatar;
						echo '<td><img id="IMG_'.$no.'"  width="100%" style="border:1px solid #C8C8C8; padding:5px; float:left;" src="'.$src.'" />
						</td>';
					}
					
						echo'<td>
								<b>NO : </b>'.$row->no_karyawan.' 
								<br/> <b>NIK : </b>'.$row->nik_karyawan.' 
								<br/> <b>NAMA : </b>'.$row->nama_karyawan.'
							</td>';
						
						echo'<td>
								<button type="button" onclick="insert_dokter('.$no.')" class="btn btn-primary btn-sm" data-dismiss="modal">Pilih</button>
							</td>';
							
						echo'<input type="hidden" id="get_tr_2_'.$no.'" name="get_tr_2_'.$no.'" value="tr_list_pasien-'.$no.'" />';
						
	echo'<input type="hidden" id="url_fix_3_'.$no.'" name="url_fix_3_'.$no.'" value="'.$src.'" />';
	echo'<input type="hidden" id="no_3_'.$no.'" name="no_3_'.$no.'" value="'.$no.'" />';
	echo'<input type="hidden" id="id_karyawan_3_'.$no.'" name="id_karyawan_3_'.$no.'" value="'.$row->id_karyawan.'" />';
	//echo'<input type="hidden" id="id_jabatan_3_'.$no.'" name="id_jabatan_3_'.$no.'" value="'.$row->id_jabatan.'" />';
	echo'<input type="hidden" id="no_karyawan_3_'.$no.'" name="no_karyawan_3_'.$no.'" value="'.$row->no_karyawan.'" />';
	echo'<input type="hidden" id="nik_karyawan_3_'.$no.'" name="nik_karyawan_3_'.$no.'" value="'.$row->nik_karyawan.'" />';
	echo'<input type="hidden" id="nama_karyawan_3_'.$no.'" name="nama_karyawan_3_'.$no.'" value="'.$row->nama_karyawan.'" />';
	
	/*
	echo'<input type="hidden" id="pnd_3_'.$no.'" name="pnd_3_'.$no.'" value="'.$row->pnd.'" />';
	echo'<input type="hidden" id="tlp_3_'.$no.'" name="tlp_3_'.$no.'" value="'.$row->tlp.'" />';
	echo'<input type="hidden" id="email_3_'.$no.'" name="email_3_'.$no.'" value="'.$row->email.'" />';
	
	echo'<input type="hidden" id="tmp_lahir_3_'.$no.'" name="tmp_lahir_3_'.$no.'" value="'.$row->tmp_lahir.'" />';
	echo'<input type="hidden" id="tgl_lahir_3_'.$no.'" name="tgl_lahir_3_'.$no.'" value="'.$row->tgl_lahir.'" />';
	echo'<input type="hidden" id="kelamin_3_'.$no.'" name="kelamin_3_'.$no.'" value="'.$row->kelamin.'" />';
	
	echo'<input type="hidden" id="avatar_3_'.$no.'" name="avatar_3_'.$no.'" value="'.$row->avatar.'" />';
	echo'<input type="hidden" id="avatar_url_3_'.$no.'" name="avatar_url_3_'.$no.'" value="'.$row->avatar_url.'" />';
	echo'<input type="hidden" id="alamat_3_'.$no.'" name="alamat_3_'.$no.'" value="'.$row->alamat.'" />';
	echo'<input type="hidden" id="ket_karyawan_3_'.$no.'" name="ket_karyawan_3_'.$no.'" value="'.$row->ket_karyawan.'" />';
	*/
	
	echo'<input type="hidden" id="lama_kerja_3_'.$no.'" name="lama_kerja_3_'.$no.'" value="'.$row->lama_kerja.'" />';
						
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
	
	function list_dokter_tersedia2_pilih_pertindakan()
	{	
		
		
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND (A.no_karyawan LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.nik_karyawan LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.nama_karyawan LIKE '%".str_replace("'","",$_POST['cari'])."%')
					AND (A.isAktif = 'DITERIMA' OR A.isAktif = '' OR A.isAktif = '0'  OR A.isAktif = 'PHK' OR A.isAktif = 'RESIGN') AND A.isDokter ='DOKTER'
					-- AND B.id_h_penjualan IS NULL
					";
		}
		else
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND (A.isAktif = 'DITERIMA' OR A.isAktif = '' OR A.isAktif = '0'  OR A.isAktif = 'PHK' OR A.isAktif = 'RESIGN') AND A.isDokter ='DOKTER'
			-- AND B.id_h_penjualan IS NULL
			";
		}
	
		
		$list_dokter = $this->M_gl_karyawan->list_karyawan_yang_tersedia_dokter($cari,$_POST['limit'],$_POST['offset']);
		//-- list_supplier_limit($cari,$_POST['limit'],$_POST['offset']);
		
		if(!empty($list_dokter))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="table_list_produk" class="table table-hover">';
				echo '<thead>
<tr style="background-color:#90EE90;color:black;font-weight:bold;">';
							echo '<th width="5%">NO</th>';
							echo '<th width="15%">FOTO</th>';
							echo '<th width="65%">BIODATA</th>';
							echo '<th width="15%">PILIH</th>';
				echo '</tr>
</thead>';
				$list_result = $list_dokter->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
					echo'<tr id="tr_list_costumer-'.$no.'" style="background-color:white;">';
						echo'<td></td>';
						echo'<td>KOSONGKAN</td>';
						echo'<td colspan="2" style="text-align:right;">
						<button id="pilihdok--" type="button" onclick="insert_dokter(this)" class="btn btn-danger btn-sm" data-dismiss="modal">HAPUS</button>
						</td>';
					echo'</tr>';
				foreach($list_result as $row)
				{
					//echo'<tr id="tr_'.$no.'">';
					echo'<tr id="tr_list_costumer-'.$no.'">';
					echo'<td>'.$no.'</td>';
					if($row->avatar == "")
					{
						$src = base_url().'assets/global/costumer/loading.gif';
						echo '<td><img id="IMG_'.$no.'"  width="100%" style="border:1px solid #C8C8C8; padding:5px; float:left;" src="'.$src.'" />
						</td>';
					}
					else
					{
						//$src = base_url().'assets/global/costumer/'.$row->avatar;
						$src = base_url().''.$row->avatar_url.''.$row->avatar;
						echo '<td><img id="IMG_'.$no.'"  width="100%" style="border:1px solid #C8C8C8; padding:5px; float:left;" src="'.$src.'" />
						</td>';
					}
					
						echo'<td>
								<b>NO : </b>'.$row->no_karyawan.' 
								<br/> <b>NIK : </b>'.$row->nik_karyawan.' 
								<br/> <b>NAMA : </b>'.$row->nama_karyawan.'
							</td>';
						
						/*
						echo'<td>
								<button type="button" onclick="insert_dokter('.$no.')" class="btn btn-primary btn-sm" data-dismiss="modal">Pilih</button>
							</td>';
						*/
						
						echo'<td>
						<button id="pilihdok-'.$row->id_karyawan.'-'.$row->nama_karyawan.'" type="button" onclick="insert_dokter(this)" class="btn btn-primary btn-sm btn-block" data-dismiss="modal">Pilih</button>
						</td>';
							
						echo'<input type="hidden" id="get_tr_2_'.$no.'" name="get_tr_2_'.$no.'" value="tr_list_pasien-'.$no.'" />';
						
	echo'<input type="hidden" id="url_fix_3_'.$no.'" name="url_fix_3_'.$no.'" value="'.$src.'" />';
	echo'<input type="hidden" id="no_3_'.$no.'" name="no_3_'.$no.'" value="'.$no.'" />';
	echo'<input type="hidden" id="id_karyawan_3_'.$no.'" name="id_karyawan_3_'.$no.'" value="'.$row->id_karyawan.'" />';
	//echo'<input type="hidden" id="id_jabatan_3_'.$no.'" name="id_jabatan_3_'.$no.'" value="'.$row->id_jabatan.'" />';
	echo'<input type="hidden" id="no_karyawan_3_'.$no.'" name="no_karyawan_3_'.$no.'" value="'.$row->no_karyawan.'" />';
	echo'<input type="hidden" id="nik_karyawan_3_'.$no.'" name="nik_karyawan_3_'.$no.'" value="'.$row->nik_karyawan.'" />';
	echo'<input type="hidden" id="nama_karyawan_3_'.$no.'" name="nama_karyawan_3_'.$no.'" value="'.$row->nama_karyawan.'" />';
	
	/*
	echo'<input type="hidden" id="pnd_3_'.$no.'" name="pnd_3_'.$no.'" value="'.$row->pnd.'" />';
	echo'<input type="hidden" id="tlp_3_'.$no.'" name="tlp_3_'.$no.'" value="'.$row->tlp.'" />';
	echo'<input type="hidden" id="email_3_'.$no.'" name="email_3_'.$no.'" value="'.$row->email.'" />';
	
	echo'<input type="hidden" id="tmp_lahir_3_'.$no.'" name="tmp_lahir_3_'.$no.'" value="'.$row->tmp_lahir.'" />';
	echo'<input type="hidden" id="tgl_lahir_3_'.$no.'" name="tgl_lahir_3_'.$no.'" value="'.$row->tgl_lahir.'" />';
	echo'<input type="hidden" id="kelamin_3_'.$no.'" name="kelamin_3_'.$no.'" value="'.$row->kelamin.'" />';
	
	echo'<input type="hidden" id="avatar_3_'.$no.'" name="avatar_3_'.$no.'" value="'.$row->avatar.'" />';
	echo'<input type="hidden" id="avatar_url_3_'.$no.'" name="avatar_url_3_'.$no.'" value="'.$row->avatar_url.'" />';
	echo'<input type="hidden" id="alamat_3_'.$no.'" name="alamat_3_'.$no.'" value="'.$row->alamat.'" />';
	echo'<input type="hidden" id="ket_karyawan_3_'.$no.'" name="ket_karyawan_3_'.$no.'" value="'.$row->ket_karyawan.'" />';
	*/
	
	echo'<input type="hidden" id="lama_kerja_3_'.$no.'" name="lama_kerja_3_'.$no.'" value="'.$row->lama_kerja.'" />';
						
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
	
	function list_therapist()
	{	
		
		
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND (A.no_karyawan LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.nik_karyawan LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.nama_karyawan LIKE '%".str_replace("'","",$_POST['cari'])."%')
					AND (A.isAktif = 'DITERIMA' OR A.isAktif = '' OR A.isAktif = '0'  OR A.isAktif = 'PHK' OR A.isAktif = 'RESIGN') AND A.isDokter ='THERAPIST'
					-- AND B.id_h_penjualan IS NULL AND C.id_h_penjualan IS NULL
					
					";
		}
		else
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND (A.isAktif = 'DITERIMA' OR A.isAktif = '' OR A.isAktif = '0'  OR A.isAktif = 'PHK' OR A.isAktif = 'RESIGN') AND A.isDokter ='THERAPIST'
			-- AND B.id_h_penjualan IS NULL AND C.id_h_penjualan IS NULL
			";
		}
	
		
		$list_dokter = $this->M_gl_karyawan->list_karyawan_yang_tersedia_therapist($cari,$_POST['limit'],$_POST['offset']);
		//-- list_supplier_limit($cari,$_POST['limit'],$_POST['offset']);
		
		if(!empty($list_dokter))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="table_list_produk" class="table table-hover">';
				echo '<thead>
<tr style="background-color:#90EE90;color:black;font-weight:bold;">';
							echo '<th width="5%">NO</th>';
							echo '<th width="15%">FOTO</th>';
							echo '<th width="65%">BIODATA</th>';
							echo '<th width="15%">PILIH</th>';
				echo '</tr>
</thead>';
				$list_result = $list_dokter->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
					echo'<tr id="tr_list_costumer-'.$no.'" style="background-color:white;">';
						echo'<td></td>';
						echo'<td>KOSONGKAN</td>';
						echo'<td colspan="2" style="text-align:right;">
						<button id="pilihdok--" type="button" onclick="insert_therapist(this)" class="btn btn-danger btn-sm" data-dismiss="modal">HAPUS</button>
						</td>';
					echo'</tr>';
				foreach($list_result as $row)
				{
					//echo'<tr id="tr_'.$no.'">';
					echo'<tr id="tr_list_costumer-'.$no.'">';
					echo'<td>'.$no.'</td>';
					if($row->avatar == "")
					{
						$src = base_url().'assets/global/costumer/loading.gif';
						echo '<td><img id="IMG_'.$no.'"  width="100%" style="border:1px solid #C8C8C8; padding:5px; float:left;" src="'.$src.'" />
						</td>';
					}
					else
					{
						//$src = base_url().'assets/global/costumer/'.$row->avatar;
						$src = base_url().''.$row->avatar_url.''.$row->avatar;
						echo '<td><img id="IMG_'.$no.'"  width="100%" style="border:1px solid #C8C8C8; padding:5px; float:left;" src="'.$src.'" />
						</td>';
					}
					
						echo'<td>
								<b>NO : </b>'.$row->no_karyawan.' 
								<br/> <b>NIK : </b>'.$row->nik_karyawan.' 
								<br/> <b>NAMA : </b>'.$row->nama_karyawan.'
								<br/> <b>PENDIDIKAN : </b>'.$row->pnd.' 
								<br/> <b>KELAMIN : </b>'.$row->kelamin.'
								<br/> <b>ALAMAT : </b>'.$row->alamat.'
								<!-- <br/> <b>LAMA KERJA : </b>'.$row->lama_kerja.' -->
							</td>';
						
						echo'<td>
								<!-- <button type="button" onclick="insert_therapist('.$no.')" class="btn btn-primary btn-sm" data-dismiss="modal">Pilih</button> -->
								
								<button id="pilihass-'.$row->id_karyawan.'-'.$row->nama_karyawan.'" type="button" onclick="insert_therapist(this)" class="btn btn-primary btn-sm btn-block" data-dismiss="modal">Pilih</button>
								
							</td>';
							
						echo'<input type="hidden" id="get_tr_2_'.$no.'" name="get_tr_2_'.$no.'" value="tr_list_pasien-'.$no.'" />';
						
	echo'<input type="hidden" id="url_fix_4_'.$no.'" name="url_fix_4_'.$no.'" value="'.$src.'" />';
	echo'<input type="hidden" id="no_4_'.$no.'" name="no_4_'.$no.'" value="'.$no.'" />';
	echo'<input type="hidden" id="id_karyawan_4_'.$no.'" name="id_karyawan_4_'.$no.'" value="'.$row->id_karyawan.'" />';
	echo'<input type="hidden" id="id_jabatan_4_'.$no.'" name="id_jabatan_4_'.$no.'" value="'.$row->id_jabatan.'" />';
	echo'<input type="hidden" id="no_karyawan_4_'.$no.'" name="no_karyawan_4_'.$no.'" value="'.$row->no_karyawan.'" />';
	echo'<input type="hidden" id="nik_karyawan_4_'.$no.'" name="nik_karyawan_4_'.$no.'" value="'.$row->nik_karyawan.'" />';
	echo'<input type="hidden" id="nama_karyawan_4_'.$no.'" name="nama_karyawan_4_'.$no.'" value="'.$row->nama_karyawan.'" />';
	echo'<input type="hidden" id="pnd_4_'.$no.'" name="pnd_4_'.$no.'" value="'.$row->pnd.'" />';
	echo'<input type="hidden" id="tlp_4_'.$no.'" name="tlp_4_'.$no.'" value="'.$row->tlp.'" />';
	echo'<input type="hidden" id="email_4_'.$no.'" name="email_4_'.$no.'" value="'.$row->email.'" />';
	
	echo'<input type="hidden" id="tmp_lahir_4_'.$no.'" name="tmp_lahir_4_'.$no.'" value="'.$row->tmp_lahir.'" />';
	echo'<input type="hidden" id="tgl_lahir_4_'.$no.'" name="tgl_lahir_4_'.$no.'" value="'.$row->tgl_lahir.'" />';
	echo'<input type="hidden" id="kelamin_4_'.$no.'" name="kelamin_4_'.$no.'" value="'.$row->kelamin.'" />';
	
	echo'<input type="hidden" id="avatar_4_'.$no.'" name="avatar_4_'.$no.'" value="'.$row->avatar.'" />';
	echo'<input type="hidden" id="avatar_url_4_'.$no.'" name="avatar_url_4_'.$no.'" value="'.$row->avatar_url.'" />';
	echo'<input type="hidden" id="alamat_4_'.$no.'" name="alamat_4_'.$no.'" value="'.$row->alamat.'" />';
	echo'<input type="hidden" id="ket_karyawan_4_'.$no.'" name="ket_karyawan_4_'.$no.'" value="'.$row->ket_karyawan.'" />';
	
	echo'<input type="hidden" id="lama_kerja_4_'.$no.'" name="lama_kerja_4_'.$no.'" value="'.$row->lama_kerja.'" />';
						
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
	
	function list_therapist2()
	{	
		
		
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND (A.no_karyawan LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.nik_karyawan LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.nama_karyawan LIKE '%".str_replace("'","",$_POST['cari'])."%')
					AND (A.isAktif = 'DITERIMA' OR A.isAktif = '' OR A.isAktif = '0'  OR A.isAktif = 'PHK' OR A.isAktif = 'RESIGN') AND A.isDokter ='THERAPIST'
					AND B.id_h_penjualan IS NULL AND C.id_h_penjualan IS NULL
					";
		}
		else
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
			AND (A.isAktif = 'DITERIMA' OR A.isAktif = '' OR A.isAktif = '0'  OR A.isAktif = 'PHK' OR A.isAktif = 'RESIGN') AND A.isDokter ='THERAPIST'
			AND B.id_h_penjualan IS NULL AND C.id_h_penjualan IS NULL
			";
		}
	
		
		$list_dokter = $this->M_gl_karyawan->list_karyawan_yang_tersedia_therapist($cari,$_POST['limit'],$_POST['offset']);
		//-- list_supplier_limit($cari,$_POST['limit'],$_POST['offset']);
		
		if(!empty($list_dokter))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="table_list_produk" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="15%">FOTO</th>';
							echo '<th width="65%">BIODATA</th>';
							echo '<th width="15%">PILIH</th>';
				echo '</tr>
</thead>';
				$list_result = $list_dokter->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					//echo'<tr id="tr_'.$no.'">';
					echo'<tr id="tr_list_costumer-'.$no.'">';
					echo'<td>'.$no.'</td>';
					if($row->avatar == "")
					{
						$src = base_url().'assets/global/costumer/loading.gif';
						echo '<td><img id="IMG_'.$no.'"  width="100%" style="border:1px solid #C8C8C8; padding:5px; float:left;" src="'.$src.'" />
						</td>';
					}
					else
					{
						//$src = base_url().'assets/global/costumer/'.$row->avatar;
						$src = base_url().''.$row->avatar_url.''.$row->avatar;
						echo '<td><img id="IMG_'.$no.'"  width="100%" style="border:1px solid #C8C8C8; padding:5px; float:left;" src="'.$src.'" />
						</td>';
					}
					
						echo'<td>
								<b>NO : </b>'.$row->no_karyawan.' 
								<br/> <b>NIK : </b>'.$row->nik_karyawan.' 
								<br/> <b>NAMA : </b>'.$row->nama_karyawan.'
								<br/> <b>PENDIDIKAN : </b>'.$row->pnd.' 
								<br/> <b>KELAMIN : </b>'.$row->kelamin.'
								<br/> <b>ALAMAT : </b>'.$row->alamat.'
								<!-- <br/> <b>LAMA KERJA : </b>'.$row->lama_kerja.' -->
							</td>';
						
						echo'<td>
								<button type="button" onclick="insert_therapist2('.$no.')" class="btn btn-primary btn-sm" data-dismiss="modal">Pilih</button>
							</td>';
							
						echo'<input type="hidden" id="get_tr_2_'.$no.'" name="get_tr_2_'.$no.'" value="tr_list_pasien-'.$no.'" />';
						
	echo'<input type="hidden" id="url_fix_5_'.$no.'" name="url_fix_5_'.$no.'" value="'.$src.'" />';
	echo'<input type="hidden" id="no_5_'.$no.'" name="no_5_'.$no.'" value="'.$no.'" />';
	echo'<input type="hidden" id="id_karyawan_5_'.$no.'" name="id_karyawan_5_'.$no.'" value="'.$row->id_karyawan.'" />';
	echo'<input type="hidden" id="id_jabatan_5_'.$no.'" name="id_jabatan_5_'.$no.'" value="'.$row->id_jabatan.'" />';
	echo'<input type="hidden" id="no_karyawan_5_'.$no.'" name="no_karyawan_5_'.$no.'" value="'.$row->no_karyawan.'" />';
	echo'<input type="hidden" id="nik_karyawan_5_'.$no.'" name="nik_karyawan_5_'.$no.'" value="'.$row->nik_karyawan.'" />';
	echo'<input type="hidden" id="nama_karyawan_5_'.$no.'" name="nama_karyawan_5_'.$no.'" value="'.$row->nama_karyawan.'" />';
	echo'<input type="hidden" id="pnd_5_'.$no.'" name="pnd_5_'.$no.'" value="'.$row->pnd.'" />';
	echo'<input type="hidden" id="tlp_5_'.$no.'" name="tlp_5_'.$no.'" value="'.$row->tlp.'" />';
	echo'<input type="hidden" id="email_5_'.$no.'" name="email_5_'.$no.'" value="'.$row->email.'" />';
	
	echo'<input type="hidden" id="tmp_lahir_5_'.$no.'" name="tmp_lahir_5_'.$no.'" value="'.$row->tmp_lahir.'" />';
	echo'<input type="hidden" id="tgl_lahir_5_'.$no.'" name="tgl_lahir_5_'.$no.'" value="'.$row->tgl_lahir.'" />';
	echo'<input type="hidden" id="kelamin_5_'.$no.'" name="kelamin_5_'.$no.'" value="'.$row->kelamin.'" />';
	
	echo'<input type="hidden" id="avatar_5_'.$no.'" name="avatar_5_'.$no.'" value="'.$row->avatar.'" />';
	echo'<input type="hidden" id="avatar_url_5_'.$no.'" name="avatar_url_5_'.$no.'" value="'.$row->avatar_url.'" />';
	echo'<input type="hidden" id="alamat_5_'.$no.'" name="alamat_5_'.$no.'" value="'.$row->alamat.'" />';
	echo'<input type="hidden" id="ket_karyawan_5_'.$no.'" name="ket_karyawan_5_'.$no.'" value="'.$row->ket_karyawan.'" />';
	
	echo'<input type="hidden" id="lama_kerja_3_'.$no.'" name="lama_kerja_3_'.$no.'" value="'.$row->lama_kerja.'" />';
						
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

	function print_antrian()
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
				if(!empty($this->uri->segment(2,0)))
				{
					$id_h_penjualan = $this->uri->segment(2,0);
					
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
								AND A.id_h_penjualan = '".$id_h_penjualan."'
								AND A.sts_penjualan = 'PENDING'
								AND (A.no_faktur LIKE '%".str_replace("'","",$_GET['cari'])."%' OR COALESCE(B.nama_lengkap,A.nama_satuan) LIKE '%".str_replace("'","",$_GET['cari'])."%')";
					}
					else
					{
						$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_h_penjualan = '".$id_h_penjualan."' AND A.sts_penjualan = 'PENDING'";
					}
					
					$order_by = "ORDER BY A.tgl_ins DESC";
					$data_h_penjualan = $this->M_gl_h_penjualan->list_pendaftaran($cari,$order_by,1,0);
					if(!empty($data_h_penjualan))
					{
						$data_h_penjualan = $data_h_penjualan->row();
						
						//GENERATE QR CODE
							$this->load->library('ciqrcode'); //pemanggilan library QR CODE
							$config['cacheable']    = true; //boolean, the default is true
							$config['cachedir']     = './assets/'; //string, the default is application/cache/
							$config['errorlog']     = './assets/'; //string, the default is application/logs/
							$config['imagedir']     = './assets/global/images/qrcode/'; //direktori penyimpanan qr code
							$config['quality']      = true; //boolean, the default is true
							$config['size']         = '1024'; //interger, the default is 1024
							$config['black']        = array(224,255,255); // array, default is array(255,255,255)
							$config['white']        = array(70,130,180); // array, default is array(0,0,0)
							$this->ciqrcode->initialize($config);
					 
							$image_name= $data_h_penjualan->no_faktur.'.png'; //buat name dari qr code sesuai dengan nim
					 
							$params['data'] = $data_h_penjualan->no_faktur; //data yang akan di jadikan QR CODE
							$params['level'] = 'H'; //H=High
							$params['size'] = 10;
							$params['savename'] = FCPATH.$config['imagedir'].$image_name; //simpan image QR CODE ke folder assets/images/
							$this->ciqrcode->generate($params); // fungsi untuk generate QR CODE
						//GENERATE QR CODE
						
						
						$cari_costumer = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_costumer = '".$data_h_penjualan->id_costumer."'";
						$data_costumer = $this->M_gl_costumer->list_costumer_biasa($cari_costumer,1,0);
						if(!empty($data_costumer))
						{
							$data_costumer = $data_costumer->row();
						}
						else
						{
							$data_costumer = false;
						}
						
						
						$id_dokter = $data_h_penjualan->id_dokter;
						$data_dokter = $this->M_gl_karyawan->get_karyawan('id_karyawan',$id_dokter);
						if(!empty($data_dokter))
						{
							$data_dokter = $data_dokter->row();
						}
						else
						{
							$data_dokter = false;
						}
						
						//echo"BERHASIl";
						
						/*TAMPILKAN*/
						
						$data = array('page_content'=>'admin/page/gl_admin_print_antrian.html','data_h_penjualan' => $data_h_penjualan,'data_dokter' => $data_dokter,'data_costumer' => $data_costumer);
						$this->load->view('admin/page/gl_admin_print_antrian.html',$data);
						
						/*TAMPILKAN*/

					}
					else
					{
						//header('Location: '.base_url().'gl-admin-pendaftaran-pasien');
						echo"GAGAL";
					}
				}
				else
				{
					header('Location: '.base_url().'gl-admin-pendaftaran-pasien');
				}
				
				
				
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function print_faktur()
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
				if(!empty($this->uri->segment(2,0)))
				{
					$id_h_penjualan = $this->uri->segment(2,0);
					
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
								AND A.id_h_penjualan = '".$id_h_penjualan."'
								AND (A.no_faktur LIKE '%".str_replace("'","",$_GET['cari'])."%' OR COALESCE(B.nama_lengkap,A.nama_satuan) LIKE '%".str_replace("'","",$_GET['cari'])."%')";
					}
					else
					{
						$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_h_penjualan = '".$id_h_penjualan."'";
					}
					
					$order_by = "ORDER BY A.tgl_ins DESC";
					$data_h_penjualan = $this->M_gl_h_penjualan->list_pendaftaran($cari,$order_by,1,0);
					if(!empty($data_h_penjualan))
					{
						$data_h_penjualan = $data_h_penjualan->row();
						
						//GENERATE QR CODE UDAH ADA
							
						//GENERATE QR CODE UDAH ADA
						
						
						$cari_costumer = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_costumer = '".$data_h_penjualan->id_costumer."'";
						$data_costumer = $this->M_gl_costumer->list_costumer_biasa($cari_costumer,1,0);
						if(!empty($data_costumer))
						{
							$data_costumer = $data_costumer->row();
						}
						else
						{
							$data_costumer = false;
						}
						
						
						$id_dokter = $data_h_penjualan->id_dokter;
						$data_dokter = $this->M_gl_karyawan->get_karyawan('id_karyawan',$id_dokter);
						if(!empty($data_dokter))
						{
							$data_dokter = $data_dokter->row();
						}
						else
						{
							$data_dokter = false;
						}
						
						//echo"BERHASIl";
						
						/*AMBIL DATA PEMBAYARAN*/
							/*CASH*/
								$cari_pembayaran_cash = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$data_h_penjualan->id_h_penjualan."' AND id_bank = '' AND cara = 'CASH' 
								-- AND DATE(tgl_ins) = DATE('".$data_h_penjualan->tgl_ins."')";
								
								$order_by_pembayaran_cash = " ORDER BY tgl_ins ASC";
								$data_pembayaran_cash = $this->M_gl_h_penjualan->list_d_penjualan_bayar_sum_nominal
										(
											$cari_pembayaran_cash,
											$order_by_pembayaran_cash,
											100,	
											0
										);
								if(!empty($data_pembayaran_cash))
								{
									$data_pembayaran_cash = $data_pembayaran_cash->row();
									//$data_pembayaran_cash = $data_pembayaran_cash->NOMINAL;
								}
								else
								{
									$data_pembayaran_cash = false;
								}
							/*CASH*/
							
							/*VIA_BANK*/
								$cari_pembayaran_bank = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$data_h_penjualan->id_h_penjualan."' AND id_bank <> '' AND cara <> 'CASH' 
								-- AND DATE(tgl_ins) = DATE('".$data_h_penjualan->tgl_ins."')
								";
								
								$order_by_pembayaran_bank = " ORDER BY tgl_ins ASC";
								$data_pembayaran_bank = $this->M_gl_h_penjualan->list_d_penjualan_bayar_sum_nominal
										(
											$cari_pembayaran_bank,
											$order_by_pembayaran_bank,
											100,	
											0
										);
								if(!empty($data_pembayaran_bank))
								{
									$data_pembayaran_bank = $data_pembayaran_bank->row();
									//$data_pembayaran_bank = $data_pembayaran_bank->NOMINAL;
								}
								else
								{
									$data_pembayaran_bank = false;
								}
							/*VIA_BANK*/
						/*AMBIL DATA PEMBAYARAN*/
						
						/*TAMPILKAN*/
						
						
						if($this->session->userdata('ses_isModePst') == "YA")
						{
							$data = array('page_content'=>'admin/page/gl_admin_print_faktur_besar.html','data_h_penjualan' => $data_h_penjualan,'data_dokter' => $data_dokter,'data_costumer' => $data_costumer,'data_pembayaran_cash'=>$data_pembayaran_cash,'data_pembayaran_bank' => $data_pembayaran_bank);
							
							$this->load->view('admin/page/gl_admin_print_faktur_besar.html',$data);
						}
						else
						{
							if($this->session->userdata('ses_jenis_faktur') == "BESAR")
							{
								if($this->session->userdata('ses_gnl_isToko') == 'Y') //MEMASTIKAN IS TOKO
								{
									$data = array('page_content'=>'admin/page/gl_admin_print_faktur_besar_toko.html','data_h_penjualan' => $data_h_penjualan,'data_dokter' => $data_dokter,'data_costumer' => $data_costumer,'data_pembayaran_cash'=>$data_pembayaran_cash,'data_pembayaran_bank' => $data_pembayaran_bank);
									
									$this->load->view('admin/page/gl_admin_print_faktur_besar_toko.html',$data);
								}
								else
								{
									$data = array('page_content'=>'admin/page/gl_admin_print_faktur.html','data_h_penjualan' => $data_h_penjualan,'data_dokter' => $data_dokter,'data_costumer' => $data_costumer,'data_pembayaran_cash'=>$data_pembayaran_cash,'data_pembayaran_bank' => $data_pembayaran_bank);
							
									$this->load->view('admin/page/gl_admin_print_faktur_besar.html',$data);
								}
								
							}
							else
							{
								$data = array('page_content'=>'admin/page/gl_admin_print_faktur.html','data_h_penjualan' => $data_h_penjualan,'data_dokter' => $data_dokter,'data_costumer' => $data_costumer,'data_pembayaran_cash'=>$data_pembayaran_cash,'data_pembayaran_bank' => $data_pembayaran_bank);
							
								$this->load->view('admin/page/gl_admin_print_faktur.html',$data);
							}
							
						}
						
						
						/*TAMPILKAN*/

					}
					else
					{
						//header('Location: '.base_url().'gl-admin-pendaftaran-pasien');
						echo"GAGAL";
					}
				}
				else
				{
					header('Location: '.base_url().'gl-admin-pendaftaran-pasien');
				}
				
				
				
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function print_surat_jalan()
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
				if(!empty($this->uri->segment(2,0)))
				{
					$id_h_penjualan = $this->uri->segment(2,0);
					
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
								AND A.id_h_penjualan = '".$id_h_penjualan."'
								AND (A.no_faktur LIKE '%".str_replace("'","",$_GET['cari'])."%' OR COALESCE(B.nama_lengkap,A.nama_satuan) LIKE '%".str_replace("'","",$_GET['cari'])."%')";
					}
					else
					{
						$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_h_penjualan = '".$id_h_penjualan."'";
					}
					
					$order_by = "ORDER BY A.tgl_ins DESC";
					$data_h_penjualan = $this->M_gl_h_penjualan->list_pendaftaran($cari,$order_by,1,0);
					if(!empty($data_h_penjualan))
					{
						$data_h_penjualan = $data_h_penjualan->row();
						
						//GENERATE QR CODE UDAH ADA
							
						//GENERATE QR CODE UDAH ADA
						
						
						$cari_costumer = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_costumer = '".$data_h_penjualan->id_costumer."'";
						$data_costumer = $this->M_gl_costumer->list_costumer_biasa($cari_costumer,1,0);
						if(!empty($data_costumer))
						{
							$data_costumer = $data_costumer->row();
						}
						else
						{
							$data_costumer = false;
						}
						
						
						$id_dokter = $data_h_penjualan->id_dokter;
						$data_dokter = $this->M_gl_karyawan->get_karyawan('id_karyawan',$id_dokter);
						if(!empty($data_dokter))
						{
							$data_dokter = $data_dokter->row();
						}
						else
						{
							$data_dokter = false;
						}
						
						//echo"BERHASIl";
						
						/*AMBIL DATA PEMBAYARAN*/
							/*CASH*/
								$cari_pembayaran_cash = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$data_h_penjualan->id_h_penjualan."' AND id_bank = '' AND cara = 'CASH' AND DATE(tgl_ins) = DATE('".$data_h_penjualan->tgl_ins."')";
								
								$order_by_pembayaran_cash = " ORDER BY tgl_ins ASC";
								$data_pembayaran_cash = $this->M_gl_h_penjualan->list_d_penjualan_bayar
										(
											$cari_pembayaran_cash,
											$order_by_pembayaran_cash,
											1,	
											0
										);
								if(!empty($data_pembayaran_cash))
								{
									$data_pembayaran_cash = $data_pembayaran_cash->row();
								}
								else
								{
									$data_pembayaran_cash = false;
								}
							/*CASH*/
							
							/*VIA_BANK*/
								$cari_pembayaran_bank = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$data_h_penjualan->id_h_penjualan."' AND id_bank <> '' AND cara <> 'CASH' AND DATE(tgl_ins) = DATE('".$data_h_penjualan->tgl_ins."')";
								
								$order_by_pembayaran_bank = " ORDER BY tgl_ins ASC";
								$data_pembayaran_bank = $this->M_gl_h_penjualan->list_d_penjualan_bayar
										(
											$cari_pembayaran_bank,
											$order_by_pembayaran_bank,
											1,	
											0
										);
								if(!empty($data_pembayaran_bank))
								{
									$data_pembayaran_bank = $data_pembayaran_bank->row();
								}
								else
								{
									$data_pembayaran_bank = false;
								}
							/*VIA_BANK*/
						/*AMBIL DATA PEMBAYARAN*/
						
						/*TAMPILKAN*/
						
						$data = array('page_content'=>'admin/page/gl_admin_print_surat_jalan.html','data_h_penjualan' => $data_h_penjualan,'data_dokter' => $data_dokter,'data_costumer' => $data_costumer,'data_pembayaran_cash'=>$data_pembayaran_cash,'data_pembayaran_bank' => $data_pembayaran_bank);
						$this->load->view('admin/page/gl_admin_print_surat_jalan.html',$data);
						
						/*TAMPILKAN*/

					}
					else
					{
						//header('Location: '.base_url().'gl-admin-pendaftaran-pasien');
						echo"GAGAL";
					}
				}
				else
				{
					header('Location: '.base_url().'gl-admin-pendaftaran-pasien');
				}
				
				
				
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	
	function print_surat_jalan_cek_dari_pusat()
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
				if(!empty($this->uri->segment(2,0)))
				{
					$id_h_penjualan = $this->uri->segment(2,0);
					
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = "WHERE A.id_h_penjualan = '".$id_h_penjualan."'
								AND (A.no_faktur LIKE '%".str_replace("'","",$_GET['cari'])."%' OR COALESCE(B.nama_lengkap,A.nama_satuan) LIKE '%".str_replace("'","",$_GET['cari'])."%')";
					}
					else
					{
						$cari = "WHERE A.id_h_penjualan = '".$id_h_penjualan."'";
					}
					
					$order_by = "ORDER BY A.tgl_ins DESC";
					$data_h_penjualan = $this->M_gl_h_penjualan->list_pendaftaran($cari,$order_by,1,0);
					if(!empty($data_h_penjualan))
					{
						$data_h_penjualan = $data_h_penjualan->row();
						
						//GENERATE QR CODE UDAH ADA
							
						//GENERATE QR CODE UDAH ADA
						
						
						$cari_costumer = " WHERE A.id_costumer = '".$data_h_penjualan->id_costumer."'";
						$data_costumer = $this->M_gl_costumer->list_costumer_biasa($cari_costumer,1,0);
						if(!empty($data_costumer))
						{
							$data_costumer = $data_costumer->row();
						}
						else
						{
							$data_costumer = false;
						}
						
						
						$id_dokter = $data_h_penjualan->id_dokter;
						$cari_dokter = "WHERE id_karyawan = '".$id_dokter."' AND kode_kantor = '".$data_h_penjualan->kode_kantor."'";
						$data_dokter = $this->M_gl_karyawan->get_karyawan_cari('id_karyawan',$id_dokter);
						if(!empty($data_dokter))
						{
							$data_dokter = $data_dokter->row();
						}
						else
						{
							$data_dokter = false;
						}
						
						//echo"BERHASIl";
						
						/*AMBIL DATA PEMBAYARAN*/
							/*CASH*/
								$cari_pembayaran_cash = " WHERE id_h_penjualan = '".$data_h_penjualan->id_h_penjualan."' AND id_bank = '' AND cara = 'CASH' ";
								
								$order_by_pembayaran_cash = " ORDER BY tgl_ins ASC";
								$data_pembayaran_cash = $this->M_gl_h_penjualan->list_d_penjualan_bayar
										(
											$cari_pembayaran_cash,
											$order_by_pembayaran_cash,
											1,	
											0
										);
								if(!empty($data_pembayaran_cash))
								{
									$data_pembayaran_cash = $data_pembayaran_cash->row();
								}
								else
								{
									$data_pembayaran_cash = false;
								}
							/*CASH*/
							
							/*VIA_BANK*/
								$cari_pembayaran_bank = " WHERE id_h_penjualan = '".$data_h_penjualan->id_h_penjualan."' AND id_bank <> '' AND cara <> 'CASH' ";
								
								$order_by_pembayaran_bank = " ORDER BY tgl_ins ASC";
								$data_pembayaran_bank = $this->M_gl_h_penjualan->list_d_penjualan_bayar
										(
											$cari_pembayaran_bank,
											$order_by_pembayaran_bank,
											1,	
											0
										);
								if(!empty($data_pembayaran_bank))
								{
									$data_pembayaran_bank = $data_pembayaran_bank->row();
								}
								else
								{
									$data_pembayaran_bank = false;
								}
							/*VIA_BANK*/
						/*AMBIL DATA PEMBAYARAN*/
						
						/*TAMPILKAN*/
						
						$data = array('page_content'=>'admin/page/gl_admin_print_surat_jalan_cek_dari_pusat.html','data_h_penjualan' => $data_h_penjualan,'data_dokter' => $data_dokter,'data_costumer' => $data_costumer,'data_pembayaran_cash'=>$data_pembayaran_cash,'data_pembayaran_bank' => $data_pembayaran_bank);
						$this->load->view('admin/page/gl_admin_print_surat_jalan_cek_dari_pusat.html',$data);
						
						/*TAMPILKAN*/

					}
					else
					{
						//header('Location: '.base_url().'gl-admin-pendaftaran-pasien');
						echo"GAGAL";
					}
				}
				else
				{
					header('Location: '.base_url().'gl-admin-pendaftaran-pasien');
				}
				
				
				
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	

	function list_pendaftaran()
	{	
		
		
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = 
			"
				WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
				AND A.sts_penjualan ='PENDING' 
				AND DATE(A.tgl_h_penjualan) = DATE(NOW())
				AND (A.no_antrian LIKE '%".str_replace("'","",$_POST['cari'])."%' OR COALESCE(B.nama_lengkap,'') LIKE '%".str_replace("'","",$_POST['cari'])."%')
			";
		}
		else
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
			AND A.sts_penjualan ='PENDING'
			AND DATE(A.tgl_h_penjualan) = DATE(NOW())
			";
		}
	
		$order_by = " ORDER BY A.tgl_ins DESC";
		$list_pendaftaran = $this->M_gl_h_penjualan->list_pendaftaran($cari,$order_by,$_POST['limit'],$_POST['offset']);
		
		if(!empty($list_pendaftaran))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="table_list_produk" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="15%">ANTRIAN</th>';
							echo '<th width="65%">BIODATA</th>';
							echo '<th width="15%">AKSI</th>';
				echo '</tr>
</thead>';
				$list_result = $list_pendaftaran->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					//echo'<tr id="tr_'.$no.'">';
					echo'<tr id="tr_list_pendaftaran-'.$no.'">';
					echo'<td>'.$no.'</td>';
					
						$src = base_url().'assets/global/images/qrcode/'.$row->no_faktur.'.png';
						echo '<td>
							<img id="IMG_barcode_'.$no.'"  width="100%" style="border:1px solid #C8C8C8; padding:5px; float:left;" src="'.$src.'" />
							<br/>
							<center>
							'.$row->no_antrian.'
							</center>
						</td>';
						
						if($row->id_h_diskon == "")
						{
							echo'<td>
									<b>NO : </b>'.$row->no_antrian.' 
									<br/> <b>NAMA : </b>'.$row->nama_costumer.'
									<br/> <b>KUNJUNGAN : </b>'.$row->type_h_penjualan.' 
								</td>';
						}
						else
						{
							echo'<td>
									<b>NO : </b>'.$row->no_antrian.' 
									<br/> <b>NAMA : </b>'.$row->nama_costumer.'
									<br/> <b>KUNJUNGAN : </b>'.$row->type_h_penjualan.' 
									<br/> <b>PAKET : </b><font style="color:red;">'.$row->nama_diskon.'</font>
								</td>';
						}
						/*TANPA ENRYPTION */
						// echo'<td>
								// <button type="button" onclick="cetak_ulang('.$row->no_antrian.')" class="btn btn-primary btn-block btn-flat">PRINT</button>
								
								// <a href="'.base_url().'gl-admin-pendaftaran-pasien/'.$row->id_h_penjualan.'" class="confirm-btn btn btn-success btn-block btn-flat" title="Apakah yakin akan menghapus antrian '.$row->no_antrian.' ?" alt="Apakah yakin akan menghapus antrian '.$row->no_antrian.' ?">UBAH</a>
								// <a href="'.base_url().'gl-admin-pendaftaran-pasien-hapus/'.$row->id_h_penjualan.'" class="confirm-btn btn btn-danger btn-block btn-flat" title="Apakah yakin akan menghapus antrian '.$row->no_antrian.' ?" alt="Apakah yakin akan menghapus antrian '.$row->no_antrian.' ?" >HAPUS</a>
							// </td>';
						/*TANPA ENRYPTION */
						
						/*DENHGAN ENRYPTION */
						echo'<td>
								<button type="button" onclick="cetak_ulang('.$row->no_antrian.')" class="btn btn-primary btn-block btn-flat">PRINT</button>
								
								<a href="'.base_url().'gl-admin-pendaftaran-pasien/'.(md5($row->id_h_penjualan)).'" class="confirm-btn btn btn-success btn-block btn-flat" title="Apakah yakin akan menghapus antrian '.$row->no_antrian.' ?" alt="Apakah yakin akan menghapus antrian '.$row->no_antrian.' ?">UBAH</a>
								<a href="'.base_url().'gl-admin-pendaftaran-pasien-hapus/'.(md5($row->id_h_penjualan)).'" class="confirm-btn btn btn-danger btn-block btn-flat" title="Apakah yakin akan menghapus antrian '.$row->no_antrian.' ?" alt="Apakah yakin akan menghapus antrian '.$row->no_antrian.' ?" >HAPUS</a>
							</td>';
						/*DENHGAN ENRYPTION */
							
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
	
	function hapus_pendaftaran()
	{
		$id_h_penjualan = ($this->uri->segment(2,0));
		//$data_h_penjualan = $this->M_gl_h_penjualan->get_h_penjualan('id_h_penjualan',$id_h_penjualan);
		$data_h_penjualan = $this->M_gl_h_penjualan->get_h_penjualan_cari(" WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(id_h_penjualan) = '".$id_h_penjualan."' ");
		if(!empty($data_h_penjualan))
		{
			$data_h_penjualan = $data_h_penjualan->row();
			
			$cari = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(id_h_penjualan) = '".$id_h_penjualan."'";
			$this->M_gl_h_penjualan->hapus($cari);
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'DELETE',
					'Melakukan penghapusan data pendaftaran '.$data_h_penjualan->no_antrian.' | '.$data_h_penjualan->no_faktur,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		
		header('Location: '.base_url().'gl-admin-pendaftaran-pasien');
	}

	function print_ulang()
	{
		$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
			AND A.sts_penjualan ='PENDING'
			AND DATE(A.tgl_h_penjualan) = DATE(NOW())
			AND A.no_antrian = '".$_POST['no_antrian']."'
			";
		
		$order_by = " ORDER BY A.tgl_ins DESC";
		$data_pendaftaran = $this->M_gl_h_penjualan->list_pendaftaran($cari,$order_by,$_POST['limit'],$_POST['offset']);
		
		if(!empty($data_pendaftaran))
		{
			$data_pendaftaran = $data_pendaftaran->row();
			echo $data_pendaftaran->id_h_penjualan;
		}
		else
		{
			return false;
		}
		
	}

	function rekam_medis()
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
				//$id_costumer = $_POST['id_costumer'];
				$no_costumer = $_POST['no_costumer'];
				/*REKAM MEDIS PASIEN*/
					//$cari_rekam_medis = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_costumer = '".$id_costumer."' AND A.sts_penjualan IN ('SELESAI','PEMBAYARAN')";
					
					//$cari_rekam_medis = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_costumer = '".$id_costumer."'";
					
					
					if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
					{
						$cari_gets = $_POST['cari'];
						
						//$cari_rekam_medis = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_costumer = '".$id_costumer."' AND A.sts_penjualan = 'SELESAI' AND (A.no_faktur = '".str_replace("'","",$_POST['cari'])."')";
						
						$cari_rekam_medis = " 
											WHERE A.no_costmer = '".$no_costumer."' 
											-- AND A.sts_penjualan = 'SELESAI' 
											AND A.sts_penjualan IN ('SELESAI','PEMBAYARAN')
											AND DATE(A.tgl_h_penjualan) > DATE_SUB(DATE(NOW()), INTERVAL 365 DAY) 
											-- AND DATE(A.tgl_h_penjualan) > DATE_SUB(DATE(NOW()), INTERVAL 150 DAY) 
											AND (A.no_faktur = '".str_replace("'","",$_POST['cari'])."')";
					}
					else
					{
						$cari_gets = "";
						
						//$cari_rekam_medis = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_costumer = '".$id_costumer."' AND A.sts_penjualan = 'SELESAI'";
						$cari_rekam_medis = " 
											WHERE A.no_costmer = '".$no_costumer."' 
											-- AND A.sts_penjualan = 'SELESAI'
											AND A.sts_penjualan IN ('SELESAI','PEMBAYARAN')
											AND DATE(A.tgl_h_penjualan) > DATE_SUB(DATE(NOW()), INTERVAL 365 DAY) 
											-- AND DATE(A.tgl_h_penjualan) > DATE_SUB(DATE(NOW()), INTERVAL 150 DAY) 
											";
					}
					
					
					
					$order_by = " ORDER BY A.tgl_ins DESC";
					
					$list_rekam_medis = $this->M_gl_h_penjualan->list_rekam_medis($cari_rekam_medis,$order_by,20,0);
					if(!empty($list_rekam_medis))
					{
						$list_result = $list_rekam_medis->result();
						foreach($list_result as $row)
						{
							if($row->avatar_dokter != "")
							//if(file_exists(base_url().''.$row->avatar_url_dokter.''.$row->avatar_dokter))
							{
								$src = base_url().''.$row->avatar_url_dokter.''.$row->avatar_dokter;
							}
							else
							{
								$src = base_url().'assets/global/costumer/loading.gif';
							}
							
							echo'
							<!-- SATU KOLOM REKAM MEDSI -->
								<li class="time-label">
									<span class="bg-red">
									'.$row->format_tgl_h_penjualan.'
									</span>
								</li>
								<li style="border-bottom:1px solid #d2d2d2;">
									<i class="fa fa-comments bg-yellow"></i>

									<div class="timeline-item">
										<span class="time"><i class="fa fa-clock-o"></i> '.$row->selisih_hari.' hari yang lalu</span>
										';
										
										//if($row->ket_penjualan == "PENJUALAN LANGSUNG")
										//{
											if($this->session->userdata('ses_isModePst') == "YA")
											{
												echo'<h3 class="timeline-header"><a href="#">'.$row->nama_karyawan.' ('.$row->no_faktur.')</a> </br><font style="color:red;">No Transaksi</font><br/>'.$row->ket_penjualan.'
												
												</h3>';
											}
											else
											{
												if($this->session->userdata('ses_gnl_isToko') == 'Y')  //MEMASTIKAN IS TOKO
												{
													echo'<h3 class="timeline-header"><a href="#">'.$row->nama_karyawan.' ('.$row->no_faktur.')</a> </br><font style="color:red;">Keterangan 1 : </font><br/>'.$row->ket_penjualan.'
													</br></br><font style="color:red;">Keterangan 2: </font><br/>'.$row->ket_penjualan2.'
													</h3>';
												}
												else
												{
													echo'<h3 class="timeline-header"><a href="#">'.$row->nama_karyawan.' ('.$row->no_faktur.')</a> </br><font style="color:red;">Keluhan</font><br/>'.$row->ket_penjualan.'
													</br></br><font style="color:red;">Diagnosa</font><br/>'.$row->ket_penjualan2.'
													</h3>';
												}
											}
										//}
										//else
										//{
											//echo'<h3 class="timeline-header"><a href="#">'.$row->nama_karyawan.' ('.$row->no_faktur.')</a> </br><font style="color:red;">Keluhan</font>'.$row->ket_penjualan.'</h3>';
										//}
										
							if($this->session->userdata('ses_isModePst') == "YA")
							{
								echo'
										<div class="timeline-body">
										<table style="width:100%;border:1 px solid black;">
											<tr>
												<td width="100%">
								';
							}
							else
							{
								echo'
										<div class="timeline-body">
										<table style="width:100%;border:1 px solid black;">
											<tr>
												<td width="20%" style="text-align:center;">
													<span id="img_span_dr">
														<img id="IMG_dr" name="IMG_dr"  width="100%" class="profile-user-img img-responsive"  src="'.$src.'" />
													</span>
													<font style="color:red;">'.$row->nama_dokter.'</font>
													
													
													<!-- <a href="'.base_url().'gl-admin-images/penjualan/'.$row->id_h_penjualan.'" class="btn btn-warning btn-flat btn-xs" >Tampilkan Foto Pemeriksaan</a> -->
													
													<a href="javascript:void(0)" id="'.$row->id_h_penjualan.'" class="btn btn-warning btn-flat btn-block" onclick="tampilkan_gambar(this);" title = "Tampilkan Gambar/Foto Pasien '.$row->no_faktur.'" alt = "Tampilkan Gambar/Foto Pasien '.$row->no_faktur.'" data-toggle="modal" data-target="#myModal_foto_pasien" >LIHAT FOTO</a>
													
													
												</td>
												<td width="80%">
								';
							}
										
							
								/*MASUKAN detail_penjualan*/
								echo'<table style="margin:1%;">';
								if((!empty($_POST['cari_d_penjualan'])) && ($_POST['cari_d_penjualan']!= "")  )
								{
									$cari_d_penjualan = "WHERE A.kode_kantor = '".$row->kode_kantor."' 
											AND A.id_h_penjualan = '".$row->id_h_penjualan."'
											AND B.isProduk <> 'CONSUMABLE'
											AND (B.kode_produk LIKE '%".str_replace("'","",$_POST['cari_d_penjualan'])."%' OR B.nama_produk LIKE '%".str_replace("'","",$_POST['cari_d_penjualan'])."%')";
								}
								else
								{
									$cari_d_penjualan = "WHERE A.kode_kantor = '".$row->kode_kantor."' 
											AND A.id_h_penjualan = '".$row->id_h_penjualan."'
											AND B.isProduk <> 'CONSUMABLE'
											";
								}
								
								$order_by = " ORDER BY A.isProduk ASC, A.tgl_ins DESC ";
								
								$list_d_penjualan = $this->M_gl_h_penjualan->list_d_penjualan($cari_d_penjualan,$order_by,1000,0);
								//($cari,$_POST['limit'],$_POST['offset']);
								
								if(!empty($list_d_penjualan))
								{
										$list_result = $list_d_penjualan->result();
										//$no =$this->uri->segment(2,0)+1;
										$no = 1;
										$isProdukCur = "";
										$isProdukOld = "";
										
										echo'
										<thead>
											<tr style="background-color:green;text-align:center;">
												<!-- <th>Select</th> -->
												<th width="25%" style="text-align:center;">KODE</th>
												<th width="30%" style="text-align:center;">NAMA PRODUK/TINDAKAN</th>
												<th width="10%" style="text-align:center;">BANYAK</th>
												<th width="35%" style="text-align:center;">ATURAN PAKAI</th>
											</tr>
										</thead>
										';
										
										foreach($list_result as $row_d_penjualan)
										{
											//echo'<tr id="tr_'.$no.'">';
											$isProdukCur = $row_d_penjualan->isProduk;
											if($isProdukCur != $isProdukOld)
											{
												echo'<tr><td colspan="4" style="color:red;">LIST '.$row_d_penjualan->isProduk.' DIPAKAI </td></tr>';
												
												$isProdukOld = $row_d_penjualan->isProduk;
											}
											
											/*
											echo'<tr id="tr_list_transaksi-'.$row_d_penjualan->id_produk.'-'.$row_d_penjualan->id_h_diskon.'" >';
												echo'<td style="text-align:left;">'.$row_d_penjualan->kode_produk.'</td>';
												echo'<td style="text-align:left;">'.$row_d_penjualan->nama_produk.'</td>';
												echo'<td style="text-align:center;">'.$row_d_penjualan->jumlah.' '.$row_d_penjualan->satuan_jual.'</td>';
												echo'<td style="text-align:left;">'.$row_d_penjualan->aturan_minum.' </td>';
											echo'</tr>';
											*/
											
											echo'<tr id="tr_list_transaksi-'.$row_d_penjualan->id_produk.'-'.$row_d_penjualan->id_h_diskon.'" >';
												echo'<td style="text-align:left;border:black 0.5px solid;padding:1%;">'.$row_d_penjualan->kode_produk.'</td>';
												echo'<td style="text-align:left;border:black 0.5px solid;padding:1%;">'.$row_d_penjualan->nama_produk.'</td>';
												echo'<td style="text-align:center;border:black 0.5px solid;padding:1%;">'.$row_d_penjualan->jumlah.' '.$row_d_penjualan->satuan_jual.'</td>';
												echo'<td style="text-align:left;border:black 0.5px solid;padding:1%;">'.$row_d_penjualan->aturan_minum.' </td>';
											echo'</tr>';
											
											$no++;
										}
										
								}
								
								
								
								echo'</table>';
								/*MASUKAN detail_penjualan*/
							
							echo'
												</td>
											</tr>
										</table>
							';
							
								/*MUNCULKAN FOTO JIKA ADA*/
								/*
								$cari_foto = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.group_by ='penjualan' AND id = '".$row->id_h_penjualan."'";
								$list_foto = $this->M_gl_images->list_images_limit($cari_foto,10,0);
								if(!empty($list_foto))
								{
									$list_result = $list_foto->result();
									foreach($list_result as $row_images)
									{
										//echo'<img src="http://placehold.it/150x100" alt="..." class="margin">';
										
										if(!(file_exists("assets/global/images/".$row_images->img_file)))
										{
											$src = base_url().'assets/global/karyawan/loading.gif';
											echo '<div style="float:left;width:100px;margin-right:1%;"><img id="IMG_'.$no.'"  width="100px" style="border:1px solid #C8C8C8; padding:5px; float:left; margin-right:20px;" src="'.$src.'" />
											<br/><font style="color:red;font-weight:bold;">'.$row_images->img_nama.'</font><br/>'.$row_images->ket_img.'</div>';
										}
										else
										{
											//$src = base_url().'assets/global/karyawan/'.$row->avatar;
											$src = base_url().''.$row_images->img_url.''.$row_images->img_file;
											echo '<div style="float:left;width:100px;margin-right:1%;"><img id="IMG_'.$no.'" width="100px"  style="border:1px solid #C8C8C8; padding:5px; float:left; margin-right:20px;" src="'.$src.'" /><br/><font style="color:red;font-weight:bold;">'.$row_images->img_nama.'</font><br/>'.$row_images->ket_img.'</div>';
										}
									}
								}
								*/
								/*MUNCULKAN FOTO JIKA ADA*/
							echo'
										</div>
										
										<!--<div class="timeline-footer">
											<a class="btn btn-warning btn-flat btn-xs" onclick="tampilkan_gambar('.$row->id_h_penjualan.')">Tampilkan Foto</a>
										</div>-->
									</div>
								</li>
							<!-- SATU KOLOM REKAM MEDSI -->
							';
						}
					}
					else
					{
						if($this->session->userdata('ses_isModePst') == "YA")
						{
							echo'<center><font style="color:red;font-weight:bold;">Belum ada histori transaksi</font></center>';
						}
						else
						{
							if($this->session->userdata('ses_gnl_isToko') == 'Y')  //MEMASTIKAN IS TOKO
							{
								echo'<center><font style="color:red;font-weight:bold;">Tidak ada data yang ditampilkan</font></center>';
							}
							else
							{
								echo'<center><font style="color:red;font-weight:bold;">Belum ada rekam medis</font></center>';
							}
							
						}
					}
				/*REKAM MEDIS PASIEN*/
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function list_harga_produk()
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
				//$id_costumer = $_POST['id_costumer'];
				$cari_produk = $_POST['cari'];
				$nama_kat_costumer = $_POST['nama_kat_costumer'];
				/*REKAM MEDIS PASIEN*/
					if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
					{
						$cari_gets = $_POST['cari'];
						$cari_produk = " 
											WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
											AND COALESCE(D.nama_kat_costumer,'') LIKE '%".str_replace("'","",$_POST['nama_kat_costumer'])."%'
											AND
											(
												A.kode_produk LIKE '%".str_replace("'","",$_POST['cari'])."%'
												OR A.nama_produk LIKE '%".str_replace("'","",$_POST['cari'])."%'
											)
											
										";
					}
					else
					{
						$cari_produk = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND COALESCE(D.nama_kat_costumer,'') LIKE '%".str_replace("'","",$_POST['nama_kat_costumer'])."%'";
					}
					
					
					$list_harga_produk = $this->M_gl_h_penjualan->list_harga_produk($cari_produk);
					if(!empty($list_harga_produk))
					{
						$list_result = $list_harga_produk->result();
						foreach($list_result as $row)
						{
							echo'<tr>';
								echo'<td>'.$row->kode_produk.'</td>';
								echo'<td>'.$row->nama_produk.'</td>';
								echo'<td>'.$row->media.'</td>';
								echo'<td>'.$row->nama_kat_costumer.'</td>';
								echo'<td>'.$row->kode_satuan.'</td>';
								echo'<td style="text-align:right;">'.number_format( $row->harga ,0,'.',',').'</td>';
							echo'</tr>';
						}
					}
					else
					{
						echo'<center><font style="color:red;font-weight:bold;">Tidak ada data yang ditampilkan</font></center>';	
					}
				/*REKAM MEDIS PASIEN*/
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}

	function tampilkan_gamar_rekam_medis()
	{
		/*AMBIL DATA GAMBAR*/
			//$cari_gambar = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.group_by = 'penjualan' AND id = '".$_POST['id_h_penjualan']."'";
			
			$cari_gambar = " WHERE A.group_by = 'penjualan' AND id = '".$_POST['id_h_penjualan']."'";
			$list_images = $this->M_gl_images->list_images_limit($cari_gambar,10,0);
			if(!empty($list_images))
			{
				$list_result = $list_images->result();
				$no=0;
				
				/*MULAI MUNCULKAN GAMBAR*/
				echo'<div id="carousel-example-generic" class="carousel slide" data-ride="carousel">';
				echo'<div id="container_foto_pasien" class="carousel-inner">';
				foreach($list_result as $row)
				{
					if($no == 0)
					{
						echo'<div class="item active">';
					}
					else
					{
						echo'<div class="item">';
					}
					
					
					echo
					'
						<img style="width:50%;margin-left:25%;" src="'.base_url().''.$row->img_url.''.$row->img_file.'" alt="'.$row->img_nama.'">
						
						</br>
						</br>
						</br>
						</br>
						</br>
						</br>
						</br>
						</br>
						<div style="padding:2%; background-color:purple;" class="carousel-caption">
							<font style="font-weight:bold;font-size:20px;">'.$row->img_nama.'</font>
							</br>
							'.$row->ket_img.'
						</div>
					</div>';
					
					$no++;
				}
				echo'</div>';
				echo'
					<a class="left carousel-control" href="#carousel-example-generic" data-slide="prev">
					<span class="fa fa-angle-left"></span>
					</a>
					<a class="right carousel-control" href="#carousel-example-generic" data-slide="next">
					<span class="fa fa-angle-right"></span>
					</a>
					</div>
				';
				echo'</div>';
				
			}
			else
			{
				$list_images = false;
			}
		/*AMBIL DATA GAMBAR*/
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_gl_admin_h_penjualan.php */

