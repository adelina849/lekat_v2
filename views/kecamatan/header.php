    <header class="main-header">
        <!-- Logo -->
        <a href="<?=base_url();?>index.php/" class="logo">
          <!-- mini logo for sidebar mini 50x50 pixels -->
          <span class="logo-mini"><b>SPK</b></span>
          <!-- logo for regular state and mobile devices -->
          <span class="logo-lg"><b>SIPEKA</b></span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
          <!-- Sidebar toggle button-->
          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
              <!-- User Account: style can be found in dropdown.less -->
              <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <img src="<?php echo $this->session->userdata('ses_avatar_url');?>" class="user-image" alt="User Image">
                  <span class="hidden-xs"><?php echo $this->session->userdata('ses_nama_karyawan');?></span>
                </a>
                <ul class="dropdown-menu">
                  <!-- User image -->
                  <li class="user-header">
                    <img src="<?php echo $this->session->userdata('ses_avatar_url');?>" class="img-circle" alt="User Image">
                    <p>
                      <?php echo $this->session->userdata('ses_nama_karyawan');?>
                      <small>Member since <?php echo $this->session->userdata('ses_tgl_ins');?></small>
                    </p>
                  </li>
                  <!-- Menu Body -->
                  <!-- Menu Footer-->
                  <li class="user-footer">
                    <div class="pull-left">
                      <!--<a href="<?php echo base_url(); ?>kec-profile" class="btn btn-default btn-flat" data-toggle="modal" data-target="#myModalProfile">Profile</a>-->
					  <a href="<?php echo base_url(); ?>kec-profile" class="btn btn-default btn-flat" >Profile</a>
                    </div>
                    <div class="pull-right">
                      <a href="<?=base_url();?>index.php/C_kec_login/logout" class="btn btn-default btn-flat">Sign out</a>
                    </div>
                  </li>
                </ul>
              </li>
              <!-- Control Sidebar Toggle Button -->
              <li>
                <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
              </li>
            </ul>
          </div>
        </nav>
      </header>
      
      