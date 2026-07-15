<?php get_header()?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>lytrod-download</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="/custom_code/download/assets/css/iepositions.css?v-2">
    <link rel="stylesheet" href="/custom_code/download/assets/css/styles.css?v=15">
</head>

<body>
<header class="entry-header">	</header>
    <div class="wrapper-one relative">
        <div class="container center-center">
            <div class="row">
                <div class="col-md-12">
                 
                    <h1 class="downloads">Downloads</h1>
                   
                    
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-md-5 admin-box" >
                <h1 class="admin-header">Make Sure You Run Download File as Administrator</h1>
                <p class="admin-p">1. Download Desired Software Install File Below. <br>2. After download completes, locate download file and right click.<br>3. Select Run as Administrator. </p>
            <img class = "admin-picture"src="/custom_code/download/assets/img/runasadmin.PNG" alt="" srcset="">
            </div>
        </div>
    </div>
  
    <div>
        <div class="container">
            <div class="row">
                <div class="col-12 table-one">
                    <h1 class="down"><strong>SoftwareDownloads</strong><br></h1>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr class="product-image">
                                    <th class="relative"><img class="product-image center-center" style="width:35%;"src="/custom_code/download/assets/img/vision-dp_logo-downloads-page.png"></th>
                                    <th class="relative"><img class="product-image center-center"style="width:48%;" src="/custom_code/download/assets/img/VisionDirect_logo_whitebgd_center.png"></th>
                                    <!-- <th class="relative"><img style="width: 80%;" class="product-image center-center" src="custom_code/download/assets/img/Intellicut-Logo-CMYK-small-300x70-1.png"></th>
                                    <th class="relative"><img style = "width:45%;" class="product-image center-center" src="custom_code/download/assets/img//VRCut_Product2.png"></th> -->
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="productname">VisionDP<br></td>
                                    <td class="productname">VisionDirect<br></td>
                                  
                                </tr>
                                                               <tr>
                                    <td class="relative last-row-height">
                                        <span class="text-center center-center full-width " >
                                            <i class="fa fa-lock"></i><i class="fa fa-download"></i>
                                            <a href = "/downloads/VDP20G-65.ZIP"class="visiondpfile">
                                                    <?php echo get_post_meta( 18535, 'vision_dp_build_name', true );?>
                                            </a>
                                         
                                        </span>
                                    </td>
                                    <td class="relative">
                                        <span class="text-center center-center full-width full-width">
                                            <i class="fa fa-download"></i>
                                            <a href="/downloads/VisionDirectInstall.exe">   
                                            <?php echo get_post_meta( 18569, 'vision_direct_build_name', true );?><br></a>
                                        </span>
                                    </td>
                                    <!-- <td class="relative">
                                        <span class="text-center center-center full-width">
                                            <i class="fa fa-download"></i>
                                            <a href="/downloads/IntellicutInstall.exe" style="text-align:center;">Intellicut Build 26<br></a>
                                        </span>
                                    </td>
                                    <td class="relative">
                                        <span class="text-center center-center full-width" >
                                            <i class="fa fa-download"></i>
                                            <a href="/install/VRCutControllerInstall.exe">VRCut Controller Build 9<br></a>
                                            <i class="fa fa-download"></i>
                                            <a href="/install/VRCutImposeInstall.exe">VRCut Impose Full Build 9<br></a>
                                        </span>
                                    </td> -->
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <!-- <th class="relative"><img class="product-image center-center" src="custom_code/download/assets/img/VisionFocus_logo-1-300x30-300x30.png" style="width: 40%"></th> -->
                                    <th class="relative"><img class="product-image center-center" src="/custom_code/download/assets/img/wiznet_logo_110x37-1.png" style="width: 13%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <!-- <td class="productname">VisionFocus<br></td> -->
                                    <td class="productname">Wiznet Installer</td>
                                </tr>
                               
                                <tr>
                                    <!-- <td class="relative" style="height: 55px;">
                                        <span class="text-center center-center full-width">
                                        <i class="fa fa-download"></i>
                                        <a href="/downloads/VisionFocusInstall.exe">
                                            VisionFocus Build 10
                                        </a>
                                        </span></td> -->
                                    <td class="relative">
                                        <span class="text-center center-center full-width" >
                                            <i class="fa fa-download"></i>
                                            <a href="https://docs.wiznet.io/img/products/wiz100sr/WIZ1xxSR_Config.zip">
                                                Wiznet Installer
                                            </a>
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-sm-4 col-md-5 col-lg-5 col-xl-2 table-three">
                    <h1 class="down">Legacy</h1>
                </div>
              
                <div class="col-md-12 col-xl-12 last-table">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr class="product-image">
                                    <th class="relative"><img class="center-center" src="/custom_code/download/assets/img/proform_logo-thumb.png"style="width: 30%"></th>
                                    <th class="relative"><img class="center-center" src="/custom_code/download/assets/img/desktop_designer_logo-thumb.png"style="width: 30%"></th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="productname">Proform Designer<br></td>
                                    <td class="productname">Desktop Designer<br></td>
                                
                                </tr>
                                <tr>
                                    <td class="relative" style="height: 55px;">
                                        <span class="text-center center-center">
                                            <i class="fa fa-lock"></i><i class="fa fa-download"></i>
                                            <a href = "/downloads/ProformDesignerInstall.exe" class="proformdesignerfile">
                                                <?php echo get_post_meta( 18593, 'proform_designer_build_name', true );?>
                                            </a>
                                        </span>
                                    </td>
                                    <td class="relative">
                                        <span class="text-center center-center">
                                        <i class="fa fa-lock"></i><i class="fa fa-download"></i>
                                        <a href="/downloads/DTD40Z-105.ZIP">DeskTop Designer Build 105<br></a>
                                        </span>
                                    </td>
                                 
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


</body>

</html>

<?php get_footer()?>
