<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class C_gl_admin_tunjangan extends CI_Controller
{
    function __construct()
    {
         parent::__construct();
         $this->load->model(array('M_gl_tunjangan','M_akun'));
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

          $data = array('page_content'=>'c_gl_admin_tunjangan/gl_admin_tunjangan');
  				$this->load->view('pusat/container',$data);

        }
        else
  			{
  				header('Location: '.base_url().'gl-admin-login');
  			}
      }
    }

    public function json() {
        header('Content-Type: application/json');
        echo $this->M_gl_tunjangan->json();
    }

    public function list_tunjangan()
    {
      $cari = $this->input->post('cari');
      $data = $this->M_gl_tunjangan->get_like($cari);

      $no=1;
      foreach ($data as $row) {
        echo '<tr>';
        echo '<td><input type="hidden" id="id_tunjangan_'.$no.'" value="'.$row->id_tunjangan.'" />'.$no.'</td>';
        echo '<td><input type="hidden" id="kode_tunjangan_'.$no.'" value="'.$row->kode_tunjangan.'" />'.$row->kode_tunjangan.'</td>';
        echo '<td><input type="hidden" id="nama_tunjangan_'.$no.'" value="'.$row->nama_tunjangan.'" />'.$row->nama_tunjangan.'</td>';
        echo '<td><input type="hidden" id="nominal_'.$no.'" value="'.$row->nominal.'" />'.$row->nominal.'</td>';
        echo '<input type="hidden" id="kode_kantor_'.$no.'" value="'.$row->kode_kantor.'" />';
        echo '<td><button type="button" class="btn btn-info" data-dismiss="modal" onclick="pilihTunjangan('.$no.')">Pilih</button></td>';
        echo '</tr>';
        $no++;
      }

    }

    public function create()
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
          $data = array(
              'button' => 'Create',
              'action' => site_url('c_gl_admin_tunjangan/create_action'),
        	    'id_tunjangan' => set_value('id_tunjangan'),
              'kode_tunjangan' => set_value('kode_tunjangan'),
        	    'nama_tunjangan' => set_value('nama_tunjangan'),
        	    'nominal' => set_value('nominal'),
        	    'ket_tunjangan' => set_value('ket_tunjangan'),
              'page_content'=>'c_gl_admin_tunjangan/gl_admin_input_tunjangan'
        	);
          $this->load->view('pusat/container',$data);
        }
        else
        {
          header('Location: '.base_url().'gl-admin-login');
        }
      }
    }

    public function create_action()
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
          $this->_rules();

          if ($this->form_validation->run() == FALSE) {
              $this->create();
          } else {
              $data = array(
                'kode_tunjangan' => $this->input->post('kode_tunjangan',TRUE),
            		'nama_tunjangan' => $this->input->post('nama_tunjangan',TRUE),
            		'nominal' => $this->input->post('nominal',TRUE),
            		'ket_tunjangan' => $this->input->post('ket_tunjangan',TRUE),
                'user_ins' => $this->session->userdata('ses_id_karyawan'),
    						'kode_kantor' => $this->session->userdata('ses_kode_kantor'),
  	          );

              $this->M_gl_tunjangan->insert($data);
              $this->session->set_flashdata('message', 'Create Record Success');
              redirect(site_url('gl-admin-tunjangan'));
          }
        }
        else
        {
          header('Location: '.base_url().'gl-admin-login');
        }
      }
    }

    public function update()
    {
        $id = $this->uri->segment(2,0);

        $row = $this->M_gl_tunjangan->get_by_id($id);

        if ($row) {
            $data = array(
                'button' => 'Update',
                'action' => site_url('c_gl_admin_tunjangan/update_action'),
            		'id_tunjangan' => set_value('id_tunjangan', $row->id_tunjangan),
                'kode_tunjangan' => set_value('kode_tunjangan', $row->kode_tunjangan),
            		'nama_tunjangan' => set_value('nama_tunjangan', $row->nama_tunjangan),
            		'nominal' => set_value('nominal', $row->nominal),
            		'ket_tunjangan' => set_value('ket_tunjangan', $row->ket_tunjangan),
                'page_content'=>'c_gl_admin_tunjangan/gl_admin_input_tunjangan'
	          );
            $this->load->view('pusat/container',$data);
        } else {
            $this->session->set_flashdata('message', 'Record Not Found');
            redirect(site_url('gl-admin-tunjangan'));
        }
    }

    public function update_action()
    {
        $this->_rules();

        if ($this->form_validation->run() == FALSE) {
            $this->update($this->input->post('id_tunjangan', TRUE));
        } else {
            $tgl = date('Y-m-d H:i:s');

            $data = array(
              'kode_tunjangan' => $this->input->post('kode_tunjangan',TRUE),
          		'nama_tunjangan' => $this->input->post('nama_tunjangan',TRUE),
          		'nominal' => $this->input->post('nominal',TRUE),
          		'ket_tunjangan' => $this->input->post('ket_tunjangan',TRUE),
          		'tgl_updt' => $tgl,
          		'user_updt' => $this->session->userdata('ses_id_karyawan'),
	           );

            $this->M_gl_tunjangan->update($this->input->post('id_tunjangan', TRUE), $data);
            $this->session->set_flashdata('message', 'Update Record Success');
            redirect(site_url('gl-admin-tunjangan'));
        }
    }

    public function delete($id)
    {
        $row = $this->M_gl_tunjangan->get_by_id($id);

        if ($row) {
            $this->M_gl_tunjangan->delete($id);
            $this->session->set_flashdata('message', 'Delete Record Success');
            redirect(site_url('gl-admin-tunjangan'));
        } else {
            $this->session->set_flashdata('message', 'Record Not Found');
            redirect(site_url('gl-admin-tunjangan'));
        }
    }

    public function _rules()
    {
      $this->form_validation->set_rules('kode_tunjangan', 'Kode tunjangan', 'trim|required');
    	$this->form_validation->set_rules('nama_tunjangan', 'nama tunjangan', 'trim|required');
    	$this->form_validation->set_rules('nominal', 'nominal', 'trim|required');
    	$this->form_validation->set_rules('ket_tunjangan', 'ket tunjangan', 'trim|required');
    	$this->form_validation->set_rules('id_tunjangan', 'id_tunjangan', 'trim');
    	$this->form_validation->set_error_delimiters('<span class="text-danger">', '</span>');
    }

}

/* End of file C_gl_admin_tunjangan.php */
/* Location: ./application/controllers/C_gl_admin_tunjangan.php */
/* Please DO NOT modify this information : */
/* Generated by Harviacode Codeigniter CRUD Generator 2020-10-08 06:42:25 */
/* http://harviacode.com */
