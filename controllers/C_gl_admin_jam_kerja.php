<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class C_gl_admin_jam_kerja extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model(array('M_gl_jam_kerja','M_akun'));
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

            $data = array('page_content'=>'c_gl_admin_jam_kerja/gl_admin_jamkerja');
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
        echo $this->M_gl_jam_kerja->json();
    }

    public function create()
    {
        $data = array(
            'button' => 'Create',
            'action' => site_url('c_gl_admin_jam_kerja/create_action'),
      	    'id_jam' => set_value('id_jam'),
      	    'nama_jam' => set_value('nama_jam'),
      	    'jam_masuk' => set_value('jam_masuk'),
      	    'jam_keluar' => set_value('jam_keluar'),
            'page_content'=>'c_gl_admin_jam_kerja/gl_admin_input_jamkerja',
            'title' => 'Input Jam Kerja',
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
          		'nama_jam' => $this->input->post('nama_jam',TRUE),
          		'jam_masuk' => $this->input->post('jam_masuk',TRUE),
          		'jam_keluar' => $this->input->post('jam_keluar',TRUE),
              'user_ins' => $this->session->userdata('ses_id_karyawan'),
              'kode_kantor' => $this->session->userdata('ses_kode_kantor'),
	           );

            $this->M_gl_jam_kerja->insert($data);
            $this->session->set_flashdata('message', 'Create Record Success');
            redirect(site_url('gl-admin-jamkerja'));
        }
    }

    public function update()
    {
        $id = $this->uri->segment(2,0);
        $row = $this->M_gl_jam_kerja->get_by_id($id);

        if ($row) {
            $data = array(
                'button' => 'Update',
                'action' => site_url('c_gl_admin_jam_kerja/update_action'),
            		'id_jam' => set_value('id_jam', $row->id_jam),
            		'nama_jam' => set_value('nama_jam', $row->nama_jam),
            		'jam_masuk' => set_value('jam_masuk', $row->jam_masuk),
            		'jam_keluar' => set_value('jam_keluar', $row->jam_keluar),
                'page_content'=>'c_gl_admin_jam_kerja/gl_admin_input_jamkerja',
                'title' => 'Update Jam Kerja',
	          );
            $this->load->view('pusat/container',$data);
        } else {
            $this->session->set_flashdata('message', 'Record Not Found');
            redirect(site_url('gl-admin-jamkerja'));
        }
    }

    public function update_action()
    {
        $this->_rules();

        if ($this->form_validation->run() == FALSE) {
            $this->update($this->input->post('id_jam', TRUE));
        } else {
            $tgl = date('Y-m-d H:i:s');
            $data = array(
          		'nama_jam' => $this->input->post('nama_jam',TRUE),
          		'jam_masuk' => $this->input->post('jam_masuk',TRUE),
          		'jam_keluar' => $this->input->post('jam_keluar',TRUE),
              'tgl_updt' => $tgl,
              'user_updt' => $this->session->userdata('ses_id_karyawan'),
	          );

            $this->M_gl_jam_kerja->update($this->input->post('id_jam', TRUE), $data);
            $this->session->set_flashdata('message', 'Update Record Success');
            redirect(site_url('gl-admin-jamkerja'));
        }
    }

    public function delete($id)
    {
        $row = $this->M_gl_jam_kerja->get_by_id($id);

        if ($row) {
            $this->M_gl_jam_kerja->delete($id);
            $this->session->set_flashdata('message', 'Delete Record Success');
            redirect(site_url('gl-admin-jamkerja'));
        } else {
            $this->session->set_flashdata('message', 'Record Not Found');
            redirect(site_url('gl-admin-jamkerja'));
        }
    }

    public function _rules()
    {
    	$this->form_validation->set_rules('nama_jam', 'nama jam', 'trim|required');
    	$this->form_validation->set_rules('jam_masuk', 'jam masuk', 'trim|required');
    	$this->form_validation->set_rules('jam_keluar', 'jam keluar', 'trim|required');

    	$this->form_validation->set_rules('id_jam', 'id_jam', 'trim');
    	$this->form_validation->set_error_delimiters('<span class="text-danger">', '</span>');
    }

}

/* End of file C_gl_admin_jam_kerja.php */
/* Location: ./application/controllers/C_gl_admin_jam_kerja.php */
/* Please DO NOT modify this information : */
/* Generated by Harviacode Codeigniter CRUD Generator 2020-10-10 14:52:49 */
/* http://harviacode.com */
