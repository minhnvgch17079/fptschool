<template>
  <div>
    <h4 class="ui-block-heading mb-5">Quản lý tài nguyên doanh nghiệp</h4>
    <div>
      <b-row class="mb-0">
        <b-col md="4">
          <b-card no-body>
            <b-card-header header-tag="h6" class="with-elements">
              <div class="card-header-title">Lương</div>
            </b-card-header>
            <div class="no-gutters row-bordered">
              <b-card-body>
                <b-row class="mb-4">
                  <b-col>
                    <i class="ion ion-ios-construct text-primary"></i>
                    <router-link to="/erp/payslip/manager" class="blink"
                    >Quản lý lương NET NVVP</router-link
                    >
                  </b-col>
                </b-row>
                <b-row class="mb-4">
                  <b-col>
                    <i class="ion ion-ios-construct text-primary"></i>
                    <router-link
                      to="/erp/partnership-payslip/manager"
                      class="blink"
                    >Quản lý lương NET nhân viên hợp tác kinh
                      doanh</router-link
                    >
                  </b-col>
                </b-row>
                <b-row class="mb-4">
                  <b-col>
                    <i class="ion ion-md-cloud-upload text-primary"></i>
                    <router-link to="/erp/payslip/adjust-upload" class="blink"
                    >Upload thông tin thưởng phạt</router-link
                    >
                  </b-col>
                </b-row>
                <b-row class="mb-4">
                  <b-col>
                    <i class="ion ion-md-ribbon text-primary"></i>
                    <router-link to='/coupon/manage' class="blink">Quản lý coupon</router-link>
                  </b-col>
                </b-row>
                <b-row class="mb-4">
                  <b-col>
                    <i class="ion ion-md-ribbon text-primary"></i>
                    <router-link to='/hrteam/work-manage' class="blink">Quản lý công viêc nhân sự</router-link>
                  </b-col>
                </b-row>
                <b-row class="mb-4">
                  <b-col>
                    <i class="ion ion-md-ribbon text-primary"></i>
                    <router-link to='/permission/manage' class="blink">Quản lý quyền nhân sự</router-link>
                  </b-col>
                </b-row>
                <b-row class="mb-4">
                  <b-col>
                    <i class="ion ion-md-mail-unread text-primary"></i>
                    <router-link to='/cods/message' class="blink">Thông báo lỗi hệ thống lương</router-link>
                  </b-col>
                </b-row>
              </b-card-body>
            </div>
          </b-card>
        </b-col>
        <b-col>
          <b-card no-body class="mb-4">
            <b-card-header header-tag="h6" class="with-elements">
              <div class="card-header-title">Nhân sự</div>
            </b-card-header>
            <div class="no-gutters row-bordered">
              <b-card-body>
                <b-row>
                  <b-col cols="6" class="mb-4">
                    <i class="ion ion-md-stats text-primary"></i>
                    <router-link to="/profile-statistic/charts" class="blink"
                    >Thống kê nhân sự</router-link
                    >
                  </b-col>
                  <b-col cols="6" class="mb-4">
                    <i class="ion ion-md-search text-primary"></i>
                    <router-link to="/staff/insurance" class="blink"
                    >Tra cứu thông tin nhân viên đóng bảo hiểm</router-link
                    >
                  </b-col>
                  <b-col cols="6" class="mb-4">
                    <i class="ion ion-ios-construct text-primary"></i>
                    <router-link to="/candidate/list" class="blink"
                    >Quản lý ứng viên</router-link
                    >
                  </b-col>
                  <b-col cols="6" class="mb-4">
                    <i class="ion ion-md-search text-primary"></i>
                    <router-link to="/staff/search" class="blink"
                    >Tra cứu thông tin nhân viên</router-link
                    >
                  </b-col>
                  <b-col cols="6" class="mb-4">
                    <i class="ion ion-ios-construct text-primary"></i>
                    <router-link to="/profile/masters" class="blink"
                    >Quản lý hồ sơ chính (master profile)</router-link
                    >
                  </b-col>
                  <b-col cols="6" class="mb-4">
                    <i class="ion ion-md-search text-primary"></i>
                    <router-link to="/ref-reward/" class="blink"
                    >Danh sách thưởng giới thiệu</router-link
                    >
                  </b-col>
                  <b-col cols="6" class="mb-4">
                    <i class="ion ion-ios-construct text-primary"></i>
                    <router-link to="/contract/manager" class="blink"
                    >Quản lý hợp đồng</router-link
                    >
                  </b-col>
                  <b-col cols="6" class="mb-4">
                    <i class="ion ion-ios-construct text-primary"></i>
                    <router-link
                      :to="{ name: 'position-detail-edit' }"
                      class="blink"
                    >Quản lý nội dung công việc</router-link
                    >
                  </b-col>
                  <b-col cols="6" class="mb-4">
                    <i class="ion ion-ios-construct text-primary"></i>
                    <router-link to="/hrteam/manage" class="blink"
                    >Cơ cấu team nhân sự</router-link
                    >
                  </b-col>
                  <b-col cols="6" class="mb-4">
                    <i class="ion ion-ios-construct text-primary"></i>
                    <router-link to="/evaluation/manage" class="blink"
                    >Quản lý thông tin đánh giá nhân viên</router-link
                    >
                  </b-col>
                  <b-col cols="6" class="mb-4">
                    <i class="ion ion-ios-construct text-primary"></i>
                    <router-link to="/shop-vote/manage" class="blink"
                    >Quản lý gán thẻ từ shop</router-link
                    >
                  </b-col>
                  <b-col cols="6" class="mb-4">
                    <i class="ion ion-ios-construct text-primary"></i>
                    <router-link to="/position/manage" class="blink"
                    >Quản lý leader bộ phận, vị trí</router-link
                    >
                  </b-col>
                  <b-col cols="6" class="mb-4">
                    <i class="ion ion-ios-construct text-primary"></i>
                    <router-link to="/dependent-person/manager" class="blink"
                    >Quản lý người phụ thuộc của nhân viên</router-link
                    >
                  </b-col>
                  <b-col cols="6" class="mb-4">
                    <i class="ion ion-ios-construct text-primary"></i>
                    <router-link to="/union/manager" class="blink"
                    >Quản lý công đoàn</router-link
                    >
                  </b-col>
                  <b-col cols="6" class="mb-4">
                    <i class="ion ion-ios-construct text-primary"></i>
                    <router-link
                      to="/salary/sgw-config-pk-part-time"
                      class="blink"
                    >Quản lý Config ca thời vụ</router-link
                    >
                  </b-col>
                  <b-col cols="6">
                    <i class="ion ion-md-ribbon text-primary"></i>
                    <router-link to='/erp/profile/template-manage' class="blink">Quản lý E - Template</router-link>
                  </b-col>
                  <b-col cols="6" class="mb-4">
                    <i class="ion ion-ios-construct text-primary"></i>
                    <router-link
                      to="/erp/document/manage"
                      class="blink"
                    >Quản lý Văn bản</router-link
                    >
                  </b-col>
                  <b-col cols="6">
                    <i class="ion ion-md-ribbon text-primary"></i>
                    <router-link to='/sos-noi-bo/xu-ly-sos' class="blink">SOS nội bộ</router-link>
                  </b-col>
                  <b-col cols="6" class="mb-4">
                    <i class="ion ion-md-ribbon text-primary"></i>
                    <a href="/admin/timesheet/manager-checkin" class="blink">Quản lý công - phép</a>
                  </b-col>
                  <b-col cols="6" class="mb-4">
                    <i class="ion ion-md-search text-primary"></i>
                    <router-link to="/insurance/order/list" class="blink">Quản lý đề xuất BHXH</router-link>
                  </b-col>
                </b-row>
              </b-card-body>
            </div>
          </b-card>
        </b-col>
      </b-row>
      <b-row class="mb-4">
        <b-col>
          <b-card no-body>
            <b-card-header header-tag="h6" class="with-elements">
              <div class="card-header-title">Xe thuê</div>
            </b-card-header>
            <div class="no-gutters row-bordered">
              <b-card-body>
                <b-row>
                  <b-col>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-ios-construct text-primary"></i>
                        <router-link to="/profile-company/masters" class="blink"
                        >Quản lý hồ sơ công ty (master profile
                          company)</router-link
                        >
                      </b-col>
                      <b-col>
                        <i class="ion ion-ios-construct text-primary"></i>
                        <router-link to="/air-line/awbs" class="blink"
                        >Quản lý AWB của công ty vận tải hàng
                          không</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-ios-construct text-primary"></i>
                        <router-link to="/truck/manage" class="blink"
                        >Quản lý xe tải</router-link
                        >
                      </b-col>
                      <b-col>
                        <i class="ion ion-md-cloud-download text-primary"></i>
                        <router-link
                          to="/salary/export-rent-truck-price"
                          class="blink"
                        >Xuất cước xe thuê</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-ios-construct text-primary"></i>
                        <router-link to="/truck-routes/manage" class="blink"
                        >Quản lý lộ trình</router-link
                        >
                      </b-col>
                      <b-col>
                        <i class="ion ion-ios-construct text-primary"></i>
                        <router-link to='/air-line/awb-price' class="blink">Quản lý AWB Price</router-link>
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-ios-construct text-primary"></i>
                        <router-link to="/train/train-bill" class="blink"
                        >Quản lý bill tàu</router-link
                        >
                      </b-col>
                      <b-col>
                        <i class="ion ion-md-cloud-upload text-primary"></i>
                        <router-link to="/truck/config-oil-limit" class="blink"
                        >Upload định mức dầu</router-link
                        >
                      </b-col>
                    </b-row>
                  </b-col>
                </b-row>
              </b-card-body>
            </div>
          </b-card>
        </b-col>
      </b-row>
      <b-row class="mb-4">
        <b-col>
          <b-card no-body>
            <b-card-header header-tag="h6" class="with-elements">
              <div class="card-header-title">Xe nhà</div>
            </b-card-header>
            <div class="no-gutters row-bordered">
              <b-card-body>
                <b-row>
                  <b-col>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-cloud-download text-primary"></i>
                        <router-link
                          to="/salary/export-driver-salary"
                          class="blink"
                        >Chạy và xuất lương tháng</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-cloud-download text-primary"></i>
                        <router-link
                          to="/salary/export-bot-driver"
                          class="blink"
                        >Xuất điểm bot tài xế</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-cloud-download text-primary"></i>
                        <router-link to='/export-driver-session-point' class="blink">Xuất dữ liệu điểm dừng tài xế</router-link>
                      </b-col>
                    </b-row>
                  </b-col>
                  <b-col>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-cloud-download text-primary"></i>
                        <router-link
                          to="/salary/review-driver-ot-session"
                          class="blink"
                        >Duyệt phiên tăng ca tài xế</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-search text-primary"></i>
                        <router-link
                          to="/salary/driver-session-data"
                          class="blink"
                        >Tìm kiếm phiên làm việc tài xế</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-ios-construct text-primary"></i>
                        <router-link to='/route/manage-difficult-route' class="blink">Quản lý Lộ Trình Khó</router-link>
                      </b-col>
                    </b-row>
                  </b-col>
                </b-row>
              </b-card-body>
            </div>
          </b-card>
        </b-col>
      </b-row>
      <b-row class="mb-4">
        <b-col>
          <b-card no-body>
            <b-card-header header-tag="h6" class="with-elements">
              <div class="card-header-title">Sale</div>
            </b-card-header>
            <div class="no-gutters row-bordered">
              <b-card-body>
                <b-row>
                  <b-col>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-search text-primary"></i>
                        <router-link to="/sales/show-salary" class="blink"
                        >Xem lương</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-search text-primary"></i>
                        <router-link
                          to="/sales/check-duplicate-shop"
                          class="blink"
                        >Kiểm tra shop trùng</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-ios-construct text-primary"></i>
                        <router-link to="/sales/manager-sale-team" class="blink"
                        >Gán Sale cho người quản lý</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-search text-primary"></i>
                        <router-link
                          to="/sales/manage-export-sale-salary"
                          class="blink"
                        >Chạy Và Xuất Bảng Lương</router-link
                        >
                      </b-col>
                    </b-row>
                  </b-col>
                  <b-col>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-ios-construct text-primary"></i>
                        <router-link to="/sales/team-process" class="blink"
                        >Gán Team-Process</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-create text-primary"></i>
                        <router-link
                          to="/sales/update-shop-package"
                          class="blink"
                        >Update shop package</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-cloud-download text-primary"></i>
                        <router-link to="/sales/export-data-shop" class="blink"
                        >Xuất data shop chi tiết</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-cloud-download text-primary"></i>
                        <router-link to="/sales/export-sale-day" class="blink"
                        >Xuất lương sale theo khoảng thời gian</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-cloud-download text-primary"></i>
                        <router-link to="/sales/check-incident-sale" class="blink"
                        >Check feebback sale</router-link
                        >
                      </b-col>
                    </b-row>
                    <!-- <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-create text-primary"></i>
                        <router-link to='/salary/wh-staff-salary-tools?id=4' class="blink">Chạy lương sale</router-link>
                      </b-col>
                    </b-row> -->
                    <!-- <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-cloud-download text-primary"></i>
                        <router-link to="/salary/wh-staff-salary-tools?id=5" class="blink">Export bảng lương sale</router-link>
                      </b-col>
                    </b-row> -->
                  </b-col>
                </b-row>
              </b-card-body>
            </div>
          </b-card>
        </b-col>
      </b-row>
      <b-row class="mb-4">
        <b-col>
          <b-card no-body>
            <b-card-header header-tag="h6" class="with-elements">
              <div class="card-header-title">COD cộng tác viên</div>
            </b-card-header>
            <div class="no-gutters row-bordered">
              <b-card-body>
                <b-row>
                  <b-col>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-ios-construct text-primary"></i>
                        <router-link to="/kpi/cods/cod-ctv-config" class="blink"
                        >Màn config lương Cộng tác viên</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-search text-primary"></i>
                        <router-link to="/cods/cod-order" class="blink"
                        >Tool thống kê đơn/ điểm COD</router-link
                        >
                      </b-col>
                    </b-row>
                  </b-col>
                  <b-col>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-cloud-download text-primary"></i>
                        <router-link to="/cods/ctv" class="blink"
                        >Tool xuất lương cộng tác viên</router-link
                        >
                      </b-col>
                    </b-row>
                  </b-col>
                </b-row>
              </b-card-body>
            </div>
          </b-card>
        </b-col>
      </b-row>
      <b-row class="mb-4">
        <b-col>
          <b-card no-body>
            <b-card-header header-tag="h6" class="with-elements">
              <div class="card-header-title">CSKH B2C</div>
            </b-card-header>
            <div class="no-gutters row-bordered">
              <b-card-body>
                <b-row>
                  <b-col>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-ios-construct text-primary"></i>
                        <router-link
                          to="/customer-cares/leader-member-b2c"
                          class="blink"
                        >Gán CSKH cho người quản lý</router-link
                        >
                      </b-col>
                      <b-col>
                        <i class="ion ion-ios-construct text-primary"></i>
                        <router-link
                          to="/customer-cares/run-export-salary-c2c"
                          class="blink"
                        >Chạy & Xuất Lương Cho Cskh C2c</router-link
                        >
                      </b-col>
                    </b-row>
                  </b-col>
                  <b-col>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-cloud-download text-primary"></i>
                        <router-link
                          to="/customer-cares/export-salary"
                          class="blink"
                        >Chạy và xuất excel bảng lương CSKH</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-cloud-download text-primary"></i>
                        <router-link
                          to="/customer-cares/export-shop-gone-away"
                          class="blink"
                        >Xem & Xuất Danh Sách Shop Rời Bỏ</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-cloud-download text-primary"></i>
                        <router-link
                          to="/customer-cares/check-data-salary"
                          class="blink"
                        >Check Dữ Liệu Lương</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-cloud-download text-primary"></i>
                        <router-link
                          to="/customer-cares/manage-shop-duplicate"
                          class="blink"
                        >Quản lý shop trùng</router-link
                        >
                      </b-col>
                    </b-row>
                  </b-col>
                </b-row>
              </b-card-body>
            </div>
          </b-card>
        </b-col>
      </b-row>
      <b-row class="mb-4">
        <b-col>
          <b-card no-body>
            <b-card-header header-tag="h6" class="with-elements">
              <div class="card-header-title">Nhân viên văn phòng</div>
            </b-card-header>
            <div class="no-gutters row-bordered">
              <b-card-body>
                <b-row>
                  <b-col>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-ios-construct text-primary"></i>
                        <router-link
                          to="/salary/quality-config-v2"
                          class="blink"
                        >Quản lý bỏ toàn trình</router-link
                        >
                      </b-col>
                    </b-row>
                  </b-col>
                  <b-col>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-cloud-download text-primary"></i>
                        <router-link to='/salary/export-salary-nv-tinh' class="blink">Export lương nhân viên điều phối</router-link>
                      </b-col>
                    </b-row>
                    <b-row>
                      <b-col>
                        <i class="ion ion-md-cloud-download text-primary"></i>
                        <router-link
                          to="/salary/export-salary-po"
                          class="blink"
                        >Export lương Nhân Viên Bưu Cục</router-link>
                      </b-col>
                    </b-row>
                  </b-col>
                </b-row>
              </b-card-body>
            </div>
          </b-card>
        </b-col>
      </b-row>
      <b-row class="mb-4">
        <b-col>
          <b-card no-body>
            <b-card-header header-tag="h6" class="with-elements">
              <div class="card-header-title">Nhân viên xử lý hàng</div>
            </b-card-header>
            <div class="no-gutters row-bordered">
              <b-card-body>
                <b-row>
                  <b-col>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-ios-construct text-primary"></i>
                        <router-link to="/profile/masters" class="blink"
                        >Quản lý profile chính của nhân viên</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-ios-construct text-primary"></i>
                        <router-link to="/bag/bag-config" class="blink"
                        >Thông tin quản lý chia bao</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-ios-construct text-primary"></i>
                        <router-link
                          to="/quality-config/ws-quality-config"
                          class="blink"
                        >Quản lý phiên toàn trình</router-link
                        >
                      </b-col>
                    </b-row>
                  </b-col>
                  <b-col>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-cloud-download text-primary"></i>
                        <router-link to="/salary/export-salary-lv" class="blink"
                        >Export lương liên vùng</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-cloud-download text-primary"></i>
                        <router-link to="/salary/export-salary-nv" class="blink"
                        >Export lương nội vùng</router-link
                        >
                      </b-col>
                    </b-row>
                  </b-col>
                </b-row>
              </b-card-body>
            </div>
          </b-card>
        </b-col>
      </b-row>
      <b-row>
        <b-col>
          <b-card no-body>
            <b-card-header header-tag="h6" class="with-elements">
              <div class="card-header-title">COD</div>
            </b-card-header>
            <div class="no-gutters row-bordered">
              <b-card-body>
                <b-row>
                  <b-col>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-create text-primary"></i>
                        <router-link to="/salary/update-area" class="blink"
                        >Update vùng đơn và xóa đơn COD</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-create text-primary"></i>
                        <router-link
                          to="/salary/update-package-area-excel"
                          class="blink"
                        >Update vùng đơn COD bằng excel</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-create text-primary"></i>
                        <router-link
                          to="/salary/update-area-by-cod"
                          class="blink"
                        >Update vùng đơn theo mã COD</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-create text-primary"></i>
                        <router-link
                          to="/salary/collection-salary-tools?id=5"
                          class="blink"
                        >Update đơn now COD và phụ cấp COD</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-ios-construct text-primary"></i>
                        <router-link to="/profile/masters" class="blink"
                        >Quản lý profile chính của nhân viên</router-link
                        >
                      </b-col>
                    </b-row>

                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-ios-construct text-primary"></i>
                        <router-link
                          to="/salary/minimum-cod-salary/"
                          class="blink"
                        >Gán mức lương tối thiểu cho COD</router-link
                        >
                      </b-col>
                    </b-row>

                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-ios-construct text-primary"></i>
                        <router-link
                          to="/manager-files/"
                          class="blink"
                        >Quản lý file cơ chế lương</router-link
                        >
                      </b-col>
                    </b-row>
                  </b-col>
                  <b-col>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-eye text-primary"></i>
                        <router-link
                          to="/salary/collection-salary-tools?id=1"
                          class="blink"
                        >Show lương COD</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-eye text-primary"></i>
                        <router-link
                          to="/salary/show-area-packages"
                          class="blink"
                        >Show vùng tính lương của COD</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-cloud-download text-primary"></i>
                        <router-link to="/salary/export-work-data" class="blink"
                        >Export bảng lương COD</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-create text-primary"></i>
                        <router-link
                          to="/salary/collection-salary-tools?id=4"
                          class="blink"
                        >Update lương tối thiểu COD</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-create text-primary"></i>
                        <router-link
                          to="/salary/collection-salary-tools?id=3"
                          class="blink"
                        >Update công thức lương COD các tháng
                          trước</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-ios-construct text-primary"></i>
                        <router-link
                          to="/salary/config-checkin-cod"
                          class="blink"
                        >Config checkin cho COD PK</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-ios-construct text-primary"></i>
                        <router-link
                          to="/salary/transit-data-work-manager"
                          class="blink"
                        >Check log dữ liệu COD trung chuyển
                          partime</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-ios-construct text-primary"></i>
                        <router-link
                          to="/salary/config-cod-index"
                          class="blink"
                        >Config chỉ số cod</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-ios-construct text-primary"></i>
                        <router-link
                          to="/salary/config-salary-cod"
                          class="blink"
                        >Config bỏ phạt cod tỉnh</router-link
                        >
                      </b-col>
                    </b-row>
                  </b-col>
                  <b-col md="5">
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-create text-primary"></i>
                        <router-link
                          to="/salary/extra-route-configs"
                          class="blink"
                        >Update lương tối thiểu và ngày công chuẩn tài xế theo
                          tuyến</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-cloud-download text-primary"></i>
                        <router-link
                          to="/salary/export-transit-cod"
                          class="blink"
                        >Tool bảng lương cod trung chuyển tỉnh</router-link
                        >
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-cloud-download text-primary"></i>
                        <router-link to="/cods/configctv" class="blink">Quản lý Cod CTV</router-link>
                      </b-col>
                    </b-row>
                    <b-row class="mb-4">
                      <b-col>
                        <i class="ion ion-md-cloud-download text-primary"></i>
                        <router-link to="/salary/cod-start" class="blink">Tool chốt lương CodStar</router-link>
                      </b-col>
                    </b-row>
                  </b-col>
                </b-row>
              </b-card-body>
            </div>
          </b-card>
        </b-col>
      </b-row>
    </div>
  </div>
</template>

<script>

export default {
    data() {
      return {
      }
    },
    components: {
    },
    created() {
    }
}
</script>

<style lang="scss">
</style>
