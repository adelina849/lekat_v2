<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class C_gl_admin_tunjangan_karyawan extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model(array('M_tunjangan_karyawan','M_akun','M_gl_hitung_gaji'));
        $this->load->library('form_validation');
	      $this->load->library('datatables');
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


          $id_karyawan = $this->uri->segment(2,0);
          $kode_kantor = $this->uri->segment(3,0);

          $header = $this->M_gl_hitung_gaji->get_karyawan($id_karyawan,$kode_kantor);
          $list_tunjangan = $this->M_tunjangan_karyawan->list_tunjangan_karyawan($id_karyawan);

          $data = array(
                  'page_content'=>'c_gl_admin_tunjangan_karyawan/input_tunjangan_karyawan',
                  'header'=>$header,
                  'id_karyawan' => $id_karyawan,
                  'list_tunjangan' => $list_tunjangan,
                  'kode_kantor' => $kode_kantor,
                );
  				$this->load->view('pusat/container',$data);
        }
        else
  			{
  				header('Location: '.base_url().'gl-admin-login');
  			}
      }
    }

    public function list_karyawan()
    {
      $cari = $this->input->post('cari');

      $data = $this->M_tunjangan_karyawan->list_karyawan($cari);

      $no=1;
      foreach ($data as $row) {
        echo '<tr>';
        echo '<input type="hidden" id="kode_kantor_'.$no.'" value="'.$row->kode_kantor.'" />';
        echo '<td><input type="hidden" id="id_karyawan_'.$no.'" value="'.$row->id_karyawan.'" />'.$no.'</td>';
        echo '<td><input type="hidden" id="no_karyawan_'.$no.'" value="'.$row->no_karyawan.'" />'.$row->no_karyawan.'</td>';
        echo '<td><input type="hidden" id="nama_karyawan_'.$no.'" value="'.$row->nama_karyawan.'" />'.$row->nama_karyawan.'</td>';
        echo '<td><input type="hidden" id="nama_kantor_'.$no.'" value="'.$row->nama_kantor.'" />'.$row->nama_kantor.'</td>';
        echo '<td><button type="button" class="btn btn-info" data-dismiss="modal" onclick="pilihKaryawan('.$no.')">Pilih</button></td>';
        echo '</tr>';
        $no++;
      }
    }

    public function set_default()
    {
      $id_karyawan = $this->input->post('id_karyawan');
      $kode_kantor = $this->input->post('kode_kantor');
      $user_ins = $this->session->userdata('ses_id_karyawan');

      $karyawan = $this->M_gl_hitung_gaji->get_karyawan($id_karyawan,$kode_kantor);

      $this->M_tunjangan_karyawan->delete_tunjangan($id_karyawan,$kode_kantor);

      if($karyawan->nama_jabatan == 'DOKTER' || $karyawan->nama_jabatan == 'PERAWAT' || $karyawan->nama_jabatan == 'THERAPIST AESTHATIC' || $karyawan->nama_jabatan == 'KOORDINATOR THERAPIST')
      {
        $this->M_tunjangan_karyawan->set_default_dokter($id_karyawan,$user_ins,$kode_kantor);  
      } else {
        $this->M_tunjangan_karyawan->set_default($id_karyawan,$user_ins,$kode_kantor);  
      }
      
    }

    public function setaktif_tunjangan()
    {
      $id_tunjangan_karyawan = $this->input->post('id_tunjangan_karyawan');
      $id_tunjangan = $this->input->post('id_tunjangan');
      $is_aktif = $this->input->post('is_aktif');
      $id_karyawan = $this->input->post('id_karyawan');
      $kode_kantor = $this->input->post('kode_kantor');
      $tgl = date('Y-m-d H:i:s');

      if($id_tunjangan_karyawan == '')
      {
        $data = array(
              'id_tunjangan' => $id_tunjangan,
              'id_karyawan' => $id_karyawan,
              'kode_kantor' => $kode_kantor,
              'nominal' => '0',
              'is_aktif' => '0',
              'kode_kantor' => $kode_kantor,
              'user_ins' => $this->session->userdata('ses_id_karyawan'),
            );

            $this->M_tunjangan_karyawan->insert($data);
      } else {

        if($is_aktif == '0')
        {
          $data = array(
            'is_aktif' => '1',
            'tgl_updt' => $tgl,
            'user_updt' => $this->session->userdata('ses_id_karyawan'),
          );

          $this->M_tunjangan_karyawan->update($id_tunjangan_karyawan, $data);  
        } else {
          $data = array(
            'is_aktif' => '0',
            'tgl_updt' => $tgl,
            'user_updt' => $this->session->userdata('ses_id_karyawan'),
          );

          $this->M_tunjangan_karyawan->update($id_tunjangan_karyawan, $data);  
        }
        
      }
    }

    public function json() {
        header('Content-Type: application/json');
        echo $this->M_tunjangan_karyawan->json();
    }

    public function create()
    {
        $data = array(
            'button' => 'Create',
            'action' => site_url('c_gl_admin_tunjangan_karyawan/create_action'),
      	    'id_tunjangan_karyawan' => set_value('id_tunjangan_karyawan'),
      	    'id_tunjangan' => set_value('id_tunjangan'),
      	    'id_karyawan' => set_value('id_karyawan'),
            'kode_kantor' => set_value('kode_kantor'),
            'nama_karyawan' => '',
            'nama_tunjangan' => '',
      	    'nominal' => set_value('nominal'),
            'page_content'=>'c_gl_admin_tunjangan_karyawan/gl_admin_input_tunjangan_karyawan',
            'title' => 'Input Tunjangan Karyawan',
      	);
        $this->load->view('pusat/container',$data);
    }

    public function create_action()
    {
        $this->_rules();

        if ($this->form_validation->run() == FALSE) {
            $this->create();
        } else {
            $data = array(
          		'id_tunjangan' => $this->input->post('id_tunjangan',TRUE),
          		'id_karyawan' => $this->input->post('id_karyawan',TRUE),
              'kode_kantor' => $this->input->post('kode_kantor',TRUE),
          		'nominal' => $this->input->post('nominal',TRUE),
          		'is_aktif' => '0',
              'user_ins' => $this->session->userdata('ses_id_karyawan'),
	          );

            $this->M_tunjangan_karyawan->insert($data);
            $this->session->set_flashdata('message', 'Create Record Success');
            redirect(site_url('gl-admin-tunjangan-karyawan'));
        }
    }



    public function update()
    {
        $id = $this->uri->segment(2,0);

        $row = $this->M_tunjangan_karyawan->get_by_id($id);

        if ($row) {
            $data = array(
                'button' => 'Update',
                'action' => site_url('c_gl_admin_tunjangan_karyawan/update_action'),
            		'id_tunjangan_karyawan' => set_value('id_tunjangan_karyawan', $row->id_tunjangan_karyawan),
            		'id_tunjangan' => set_value('id_tunjangan', $row->id_tunjangan),
            		'id_karyawan' => set_value('id_karyawan', $row->id_karyawan),
                'kode_kantor' => set_value('kode_kantor', $row->kode_kantor),
                'nama_karyawan' => set_value('nama_karyawan', $row->nama_karyawan),
                'nama_tunjangan' => set_value('nama_tunjangan', $row->nama_tunjangan),
            		'nominal' => set_value('nominal', $row->nominal),
            		'is_aktif' => set_value('is_aktif', $row->is_aktif),
                'page_content'=>'c_gl_admin_tunjangan_karyawan/gl_admin_input_tunjangan_karyawan',
                'title' => 'Update Tunjangan Karyawan',
	          );
            $this->load->view('pusat/container',$data);
        } else {
            $this->session->set_flashdata('message', 'Record Not Found');
            redirect(site_url('gl-admin-tunjangan-karyawan'));
        }
    }

    public function update_action()
    {
        $this->_rules();

        if ($this->form_validation->run() == FALSE) {
            $this->update($this->input->post('id_tunjangan_karyawan', TRUE));
        } else {
            $tgl = date('Y-m-d H:i:s');
            $data = array(
          		'id_tunjangan' => $this->input->post('id_tunjangan',TRUE),
          		'id_karyawan' => $this->input->post('id_karyawan',TRUE),
              'kode_kantor' => $this->input->post('kode_kantor',TRUE),
          		'nominal' => $this->input->post('nominal',TRUE),
          		'tgl_updt' => $tgl,
          		'user_updt' => $this->session->userdata('ses_id_karyawan'),
	          );

            $this->M_tunjangan_karyawan->update($this->input->post('id_tunjangan_karyawan', TRUE), $data);
            $this->session->set_flashdata('message', 'Update Record Success');
            redirect(site_url('gl-admin-tunjangan-karyawan'));
        }
    }

    public function update_tunjangan()
    {
       
       $tgl = date('Y-m-d H:i:s');
       $data = array(
          'nominal' => $this->input->post('nominal',TRUE),
          'tgl_updt' => $tgl,
          'user_updt' => $this->session->userdata('ses_id_karyawan'),
        );

        $this->M_tunjangan_karyawan->update($this->input->post('id_tunjangan_karyawan', TRUE), $data);
    }


    public function delete($id)
    {
        //$row = $this->M_tunjangan_karyawan->get_by_id($id);

        //if ($row) {
            $this->M_tunjangan_karyawan->delete($id);
            $this->session->set_flashdata('message', 'Delete Record Success');
            redirect(site_url('gl-admin-tunjangan-karyawan'));
        //} else {
        //    $this->session->set_flashdata('message', 'Record Not Found');
        //    redirect(site_url('gl-admin-tunjangan-karyawan'));
        //}
    }

    public function _rules()
    {
    	$this->form_validation->set_rules('id_tunjangan', 'id tunjangan', 'trim|required');
    	$this->form_validation->set_rules('id_karyawan', 'id karyawan', 'trim|required');
    	$this->form_validation->set_rules('nominal', 'nominal', 'trim|required');

    	$this->form_validation->set_rules('id_tunjangan_karyawan', 'id_tunjangan_karyawan', 'trim');
    	$this->form_validation->set_error_delimiters('<span class="text-danger">', '</span>');
    }

}

/* End of file C_gl_admin_tunjangan_karyawan.php */
/* Location: ./application/controllers/C_gl_admin_tunjangan_karyawan.php */
/* Please DO NOT modify this information : */
/* Generated by Harviacode Codeigniter CRUD Generator 2020-10-08 06:44:29 */
/* http://harviacode.com */
