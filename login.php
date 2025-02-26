<!DOCTYPE html>

<html lang="en">

	<!-- begin::Head -->
	<head>
		<base href="">
		<meta charset="utf-8" />
		<title>AxisPro</title>
		<meta name="description"
              content="AxisPro Cloud ERP System | Direct Axis Technology L.L.C | Cloud solutions for AMER,TAS-HEEL,TAD-BEER and HR">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!--begin::Fonts -->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700|Asap+Condensed:500">

		<!--end::Fonts -->

		<link rel="shortcut icon" href="assets/media/logos/favicon.ico" />
		<!--begin::Page Custom Styles(used by this page) -->
        <link rel="stylesheet" href="v3/plugins/global/plugins.bundle.css?id=ef48222f6f965b39719e4f347c3f987d">
        <link rel="stylesheet" href="v3/plugins/global/plugins-custom.bundle.css?id=f6d521c7a3e20529112555af5fd62808">
		<link href="assets/css/style.bundle.css?id=v1.0.1" rel="stylesheet" type="text/css" />
		<link href="assets/css/pages/login/login-4.css?id=v1.0.1" rel="stylesheet" type="text/css" />



		<style>


			.kt-login.kt-login--v4 .kt-login__wrapper .kt-login__container .kt-login__logo {

				margin: 0 !important;

			}

			.kt-login.kt-login--v4 .kt-login__wrapper .kt-login__container .kt-form .form-control {
				border: 1px solid #ccc !important;
			}

		</style>


	</head>

	<!-- end::Head -->

	<!-- begin::Body -->
	<body class="kt-page-content-white kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-page--loading">

		<!-- begin:: Page -->
		<div class="kt-grid kt-grid--ver kt-grid--root kt-page">
			<div class="kt-grid kt-grid--hor kt-grid--root  kt-login kt-login--v4 kt-login--signin" id="kt_login">
				<div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor">
					<div class="kt-grid__item kt-grid__item--fluid kt-login__wrapper d-flex align-items-center justify-content-center m-0">
						<div class="kt-login__container p-5 border rounded">
							<div class="kt-login__logo">
								<a href="#">
									<img style="width: 218px" src="assets/media/logos/logo-10.png">
								</a>
							</div>
							<div class="kt-login__signin">
								<div class="kt-login__head">
									<h3 class="kt-login__title">Sign In</h3>
								</div>
								<form class="kt-form" action="ERP/" method="post">
									<div class="form-group">
										<input
                                            required
                                            class="form-control"
                                            type="text"
                                            placeholder="User Name"
                                            name="user_name_entry_field"
                                            autocomplete="off">
									</div>
									<div class="form-group">
										<input
                                            required
                                            class="form-control"
                                            type="password"
                                            placeholder="Password"
                                            name="password">
									</div>

                                    <input type="hidden" name="company_login_name" value="0">

									<div class="kt-login__actions">
										<button type="submit" id="kt_login_signin_submit" name="Login" class="btn btn-brand btn-pill kt-login__btn-primary">Sign In</button>
									</div>
								</form>
							</div>

						</div>
					</div>
				</div>
			</div>
		</div>
		<iframe src="https://axisproerp.com/notify_clients/index.php" width="100%" height="300" frameborder="0" style="padding:2%;"></iframe>
		<!-- end:: Page -->

		<!-- begin::Global Config(global config for global JS sciprts) -->
		<script>
			var KTAppOptions = {
				"colors": {
					"state": {
						"brand": "#5d78ff",
						"light": "#ffffff",
						"dark": "#282a3c",
						"primary": "#5867dd",
						"success": "#34bfa3",
						"info": "#36a3f7",
						"warning": "#ffb822",
						"danger": "#fd3995"
					},
					"base": {
						"label": ["#c5cbe3", "#a1a8c3", "#3d4465", "#3e4466"],
						"shape": ["#f0f3ff", "#d9dffa", "#afb4d4", "#646c9a"]
					}
				}
			};
		</script>

		<!-- end::Global Config -->

		<!--begin::Global Theme Bundle(used by all pages) -->

        <script src="v3/plugins/global/plugins.bundle.js?id=c19789311d3313275449575f34d2dab7"></script>
        <script src="assets/js/scripts.bundle.js?id=v1.0.3" type="text/javascript"></script>
        <script src="v3/js/fa-scripts.bundle.js?id=020eb079bb78f75fd8bd75d343fec61e"></script>

		<!--begin::Page Scripts(used by this page) -->
		<script src="assets/js/pages/custom/login/login-general.js?id=v1.1.2" type="text/javascript"></script>

		<!--end::Page Scripts -->
	</body>

	<!-- end::Body -->
</html>