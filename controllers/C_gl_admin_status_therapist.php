<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_status_therapist extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_h_penjualan','M_gl_lap_penjualan'));
		
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
				
				
				$msgbox_title = " Status Therapist";
				
				//$data = array('page_content'=>'gl_admin_status_therapist','halaman'=>$halaman,'list_therapist'=>$list_therapist,'msgbox_title' => $msgbox_title);
				
				$data = array('page_content'=>'gl_admin_status_therapist','msgbox_title' => $msgbox_title);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function list_status_therapist_ajax()
	{
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND (A.no_karyawan LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.nik_karyawan LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.nama_karyawan LIKE '%".str_replace("'","",$_POST['cari'])."%')
					AND (A.isAktif = 'DITERIMA' OR A.isAktif = '' OR A.isAktif = '0'  OR A.isAktif = 'PHK' OR A.isAktif = 'RESIGN') AND A.isDokter ='THERAPIST'
					";
		}
		else
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND (A.isAktif = 'DITERIMA' OR A.isAktif = '' OR A.isAktif = '0'  OR A.isAktif = 'PHK' OR A.isAktif = 'RESIGN') AND A.isDokter ='THERAPIST'
			";
		}
		
		/*
		$this->load->library('pagination');
		//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
		//$config['base_url'] = base_url().'admin/jabatan/';
		
		$config['first_url'] = site_url('gl-admin-status-therapist?'.http_build_query($_GET));
		$config['base_url'] = site_url('gl-admin-status-therapist/');
		$config['total_rows'] = $this->M_gl_lap_penjualan->count_status_karyawan_tersedia_limit($cari)->JUMLAH;
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
		
		
		$list_therapist = $this->M_gl_lap_penjualan->list_status_karyawan_therapist_tersedia($cari,$config['per_page'],$this->uri->segment(2,0));
		*/
		
		$list_therapist = $this->M_gl_lap_penjualan->list_status_karyawan_therapist_tersedia($cari,1000,0);
		
		
		
		//TABLE
		if(!empty($list_therapist))
			{
				//echo gethostname();
				//echo $this->M_gl_pengaturan->getUserIpAddr();
				//$sts_query = strpos(base_url(),"localhost");
				//echo $sts_query;
				//$nama = "Mulyana Yusuf";
				//echo str_replace("f","849",$nama);
				echo'<table width="100%" id="example2" class="table table-hover">';
					echo '<thead>
<tr>';
								echo '<th width="5%">NO</th>';
								echo '<th width="10%">FOTO</th>';
								echo '<th width="35%">BIODATA</th>';
								echo '<th width="35%">STATUS</th>';
								echo '<th width="15%">AKSI</th>';
					echo '</tr>
</thead>';
					$list_result = $list_therapist->result();
					$no =$this->uri->segment(2,0)+1;
					echo '<tbody>';
					foreach($list_result as $row)
					{
						echo'<tr>';
							echo'<td>'.$no.'</td>';
							
							if($row->avatar == "")
							{
								$src = base_url().'assets/global/karyawan/loading.gif';
								echo '<td><img id="IMG_'.$no.'"  width="100px" height="100px" style="border:1px solid #C8C8C8; padding:5px; float:left; margin-right:20px;" src="'.$src.'" />
								</td>';
							}
							else
							{
								//$src = base_url().'assets/global/karyawan/'.$row->avatar;
								$src = base_url().''.$row->avatar_url.''.$row->avatar;
								echo '<td><img id="IMG_'.$no.'"  width="100px" height="100px" style="border:1px solid #C8C8C8; padding:5px; float:left; margin-right:20px;" src="'.$src.'" />
								</td>';
							}
							
							
							echo'<td>
								<b>NO : </b>'.$row->no_karyawan.' 
								<br/> <b>NIK : </b>'.$row->nik_karyawan.' 
								<br/> <b>NAMA : </b>'.$row->nama_karyawan.'
							</td>';
							
							echo'<td>
								<b>NO FAKTUR : </b>'.$row->no_faktur_ass.' 
								<br/> <b>NO PASIEN : </b>'.$row->no_costumer.' 
								<br/> <b>NAMA : </b>'.$row->nama_costumer.'
							</td>';
							
							echo'<td>';
							if($row->no_costumer != "")
							{
								//if($row->id_ass_dok == $this->session->userdata('ses_id_karyawan'))
								if( ($row->id_ass_dok == $this->session->userdata('ses_id_karyawan')) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
									if($row->sts_penjualan =="SELESAI")
									{
										/*
										if( ($row->id_dokter == $this->session->userdata('ses_id_karyawan')) or ($this->session->userdata('ses_akses_lvl2_54') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
										{
										}
										else
										*/
										echo'
											<a style="background-color:red;color:black; font-weight:bold;" href="#" id="selesai-'.$row->id_h_penjualan.'" alt="Apakah anda yakin akan menyelesaikan transaksi ini ?" title="Apakah anda yakin akan menyelesaikan transaksi ini ?" class="confirm-btn btn btn-success btn-flat btn-block" onclick="selesai_tindakan(this)"> TRANSAKSI SUDAH DI TUTUP </a>
										</td>';
									}
									else
									{
										echo'
											<a style="color:black; font-weight:bold;" href="#" id="selesai-'.$row->id_h_penjualan.'" alt="Apakah anda yakin akan menyelesaikan transaksi ini ?" title="Apakah anda yakin akan menyelesaikan transaksi ini ?" class="confirm-btn btn btn-success btn-flat btn-block" onclick="selesai_tindakan(this)"> SELESAI TINDAKAN </a>
										</td>';
									}
								}
								else
								{
									echo'<td></td>';
								}
							}
							else
							{
								echo'<td></td>';
							}
							
							//echo'<td>';
							echo'<input type="hidden" id="id_dokter2-'.$row->id_h_penjualan.'" name="id_dokter2-'.$row->id_h_penjualan.'" value="'.$row->id_dokter.'" />';
						
							echo'<input type="hidden" id="id_ass-'.$row->id_h_penjualan.'" name="id_ass-'.$row->id_h_penjualan.'" value="'.$row->id_ass_dok1.'" />';
							
							echo'<input type="hidden" id="id_ass2-'.$row->id_h_penjualan.'" name="id_ass2-'.$row->id_h_penjualan.'" value="'.$row->id_ass_dok2.'" />';
							
							echo'<input type="hidden" id="lama_kerja_dokter2-'.$row->id_h_penjualan.'" name="lama_kerja_dokter2-'.$row->id_h_penjualan.'" value="'.$row->lama_dokter2.'" />';
							
							echo'<input type="hidden" id="lama_kerja_ass-'.$row->id_h_penjualan.'" name="lama_kerja_ass-'.$row->id_h_penjualan.'" value="'.$row->lama_kerja.'" />';
							
							echo'<input type="hidden" id="lama_kerja_ass2-'.$row->id_h_penjualan.'" name="lama_kerja_ass2-'.$row->id_h_penjualan.'" value="'.$row->lama_kerja.'" />';
							
							echo'<input type="hidden" id="type_h_penjualan-'.$row->id_h_penjualan.'" name="type_h_penjualan-'.$row->id_h_penjualan.'" value="'.$row->type_h_penjualan.'" />';
							//echo'</td>';
							
						echo'</tr>';
						
						
						
						
						$no++;
					}
					
					echo '</tbody>';
				echo'</table>';
			}
			else
			{
				echo'<center>';
				echo'Tidak Ada Data Yang Ditampilkan !';
				echo'</center>';
			}
		//TABLE
		
		/*
		<center>
			<div class="halaman"><?php echo $halaman;?></div>
		</center>
		*/
	}
	
	public function status_dokter()
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
				
				$msgbox_title = " Status Dokter";
				
				$data = array('page_content'=>'gl_admin_status_dokter','msgbox_title' => $msgbox_title);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function list_status_dokter_ajax()
	{
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "
						WHERE COALESCE(B.id_dok,'') <> '' 
							AND  COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
							AND DATE(A.tgl_ins) = DATE(NOW())
							AND 
								(
									A.no_faktur LIKE '%".str_replace("'","",$_POST['cari'])."%'
									OR A.nama_costumer LIKE '%".str_replace("'","",$_POST['cari'])."%'
									OR COALESCE(B.nama_dok,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
								)
					";
		}
		else
		{
			$cari = "WHERE COALESCE(B.id_dok,'') <> '' 
							AND  COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
							AND DATE(A.tgl_ins) = DATE(NOW())
			";
		}
		
		
		
		//$list_status_dokter = $this->M_gl_lap_penjualan->list_status_karyawan_dokter_tersedia($cari,1000,0);
		$list_status_dokter = $this->M_gl_lap_penjualan->list_status_dokter($cari,1000,0);
		
		//TABLE
		if(!empty($list_status_dokter))
			{
				echo'<table width="100%" id="example2" class="table table-hover">';
					echo '<thead>
<tr>';
								echo '<th width="5%">NO</th>';
								echo '<th width="10%">NO FAKTUR</th>';
								echo '<th width="35%">PASIEN</th>';
								echo '<th width="35%">DOKTER</th>';
								echo '<th width="15%">STATUS</th>';
					echo '</tr>
</thead>';
					$list_result = $list_status_dokter->result();
					$no =$this->uri->segment(2,0)+1;
					echo '<tbody>';
					foreach($list_result as $row)
					{
						echo'<tr>';
							echo'<td>'.$no.'</td>';
							echo'<td>'.$row->no_faktur.'</td>';
							echo'<td>'.$row->nama_costumer.'</td>';
							echo'<td>'.$row->nama_dok.'</td>';
							
							if($row->sts_penjualan == 'PEMBAYARAN')
							{
								//echo'<td style="color:yellow;font-weight:bold;">'.$row->sts_penjualan.'</td>';
								if( ($row->id_dok == $this->session->userdata('ses_id_karyawan')) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
								{
									echo'
										<td>
											<a style="color:black; font-weight:bold;" href="#" id="selesai-'.$row->id_h_penjualan.'" alt="Apakah anda yakin akan menyelesaikan transaksi ini ?" title="Apakah anda yakin akan menyelesaikan transaksi ini ?" class="confirm-btn btn btn-success btn-flat btn-block" onclick="selesai_tindakan(this)"> SELESAI TINDAKAN </a>
										</td>
									';
								}
								else
								{
									echo'<td style="color:yellow;font-weight:bold;">'.$row->sts_penjualan.'</td>';
								}
							}
							elseif($row->sts_penjualan == 'SELESAI')
								echo'<td style="color:green;font-weight:bold;">'.$row->sts_penjualan.'</td>';
							else
							{
								echo'<td style="color:red;font-weight:bold;">'.$row->sts_penjualan.'</td>';
							}
							
							
							
						echo'</tr>';
						
						
						
						
						$no++;
					}
					
					echo '</tbody>';
				echo'</table>';
			}
			else
			{
				echo'<center>';
				echo'Tidak Ada Data Yang Ditampilkan !';
				echo'</center>';
			}
		//TABLE
							
	}
	
	function list_status_dokter_ajax_old()
	{
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND (A.no_karyawan LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.nik_karyawan LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.nama_karyawan LIKE '%".str_replace("'","",$_POST['cari'])."%')
					AND (A.isAktif = 'DITERIMA' OR A.isAktif = '' OR A.isAktif = '0'  OR A.isAktif = 'PHK' OR A.isAktif = 'RESIGN') AND A.isDokter ='DOKTER'
					";
		}
		else
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND (A.isAktif = 'DITERIMA' OR A.isAktif = '' OR A.isAktif = '0'  OR A.isAktif = 'PHK' OR A.isAktif = 'RESIGN') AND A.isDokter ='DOKTER'
			";
		}
		
		
		
		$list_status_dokter = $this->M_gl_lap_penjualan->list_status_karyawan_dokter_tersedia($cari,1000,0);
		
		//TABLE
		if(!empty($list_status_dokter))
			{
				//echo gethostname();
				//echo $this->M_gl_pengaturan->getUserIpAddr();
				//$sts_query = strpos(base_url(),"localhost");
				//echo $sts_query;
				//$nama = "Mulyana Yusuf";
				//echo str_replace("f","849",$nama);
				echo'<table width="100%" id="example2" class="table table-hover">';
					echo '<thead>
<tr>';
								echo '<th width="5%">NO</th>';
								echo '<th width="10%">FOTO</th>';
								echo '<th width="40%">BIODATA</th>';
								echo '<th width="45%">STATUS</th>';
					echo '</tr>
</thead>';
					$list_result = $list_status_dokter->result();
					$no =$this->uri->segment(2,0)+1;
					echo '<tbody>';
					foreach($list_result as $row)
					{
						echo'<tr>';
							echo'<td>'.$no.'</td>';
							
							if($row->avatar == "")
							{
								$src = base_url().'assets/global/karyawan/loading.gif';
								echo '<td><img id="IMG_'.$no.'"  width="100px" height="100px" style="border:1px solid #C8C8C8; padding:5px; float:left; margin-right:20px;" src="'.$src.'" />
								</td>';
							}
							else
							{
								//$src = base_url().'assets/global/karyawan/'.$row->avatar;
								$src = base_url().''.$row->avatar_url.''.$row->avatar;
								echo '<td><img id="IMG_'.$no.'"  width="100px" height="100px" style="border:1px solid #C8C8C8; padding:5px; float:left; margin-right:20px;" src="'.$src.'" />
								</td>';
							}
							
							
							echo'<td>
								<b>NO : </b>'.$row->no_karyawan.' 
								<br/> <b>NIK : </b>'.$row->nik_karyawan.' 
								<br/> <b>NAMA : </b>'.$row->nama_karyawan.'
							</td>';
							
							echo'<td>
								<b>NO FAKTUR : </b>'.$row->no_faktur_ass.' 
								<br/> <b>NO PASIEN : </b>'.$row->no_costumer.' 
								<br/> <b>NAMA : </b>'.$row->nama_costumer.'
							</td>';
							
						echo'</tr>';
						
						
						
						
						$no++;
					}
					
					echo '</tbody>';
				echo'</table>';
			}
			else
			{
				echo'<center>';
				echo'Tidak Ada Data Yang Ditampilkan !';
				echo'</center>';
			}
		//TABLE
							
	}
	
	function selesai_sts_penjualan()
	{
		$this->M_gl_h_penjualan->ubah_h_penjualan_sts_saja2($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],"SELESAI");
						
		$balikan = "SELESAI";
		
		echo'SELESAI';
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */