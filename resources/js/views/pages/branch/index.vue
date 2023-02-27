<script>
import Layout from "../../layouts/main";
import PageHeader from "../../../components/Page-header";

/**
 * Advanced Table component
 */
export default {
  page: {
    title: "Branch",
    meta: [{ name: "description", content: 'kdjsd' }],
  },
  components: {
    Layout,
    PageHeader,
  },
  data() {
    return {
        title: "Branch",
        items: [
            {
                text: "Minton",
                href: "/",
            },
            {
                text: "Branches",
                active: true,
            },
        ],
        revenueData: [
            {
                marketplaces: "Themes Market",
                date: "Oct 15, 2018",
                tax: "$125.23",
                payout: "$5848.68",
                status: "Upcoming"
            },
            {
                marketplaces: "Freelance",
                date: "Oct 12, 2018",
                tax: "$78.03",
                payout: "$5848.68",
                status: "Paid"
            },
            {
                marketplaces: "Share Holding",
                date: "Oct 10, 2018",
                tax: "$358.24",
                payout: "$815.89",
                status: "Paid"
            },
            {
                marketplaces: "Envato's Affiliates",
                date: "Oct 03, 2018",
                tax: "$18.78",
                payout: "$248.75",
                status: "Overdue"
            },
            {
                marketplaces: "Marketing Revenue",
                date: "Sep 21, 2018",
                tax: "$185.36",
                payout: "$978.21",
                status: "Upcoming"
            },
            {
                marketplaces: "Advertise Revenue",
                date: "Sep 15, 2018",
                tax: "$29.56",
                payout: "$358.10",
                status: "Paid"
            }
        ]
    }
  },
  computed: {
    /**
     * Total no. of records
     */

  },
  mounted() {

  },
  methods: {
    /**
     * Search the table data with search input
     */

  },
};
</script>

<template>
  <Layout>
    <PageHeader :title="title" :items="items" />
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <div class="row justify-content-between align-items-center mb-3">
                <h4 class="header-title">Companies Table</h4>
                <b-button
                    v-b-modal.modal-responsive
                    variant="success"
                >
                    Create
                </b-button>
            </div>

              <div class="row justify-content-between align-items-center mb-3">
                  <div class="col-lg-3 col-6" style="font-weight: 500">
                      Show
                      <select
                          class="custom-select custom-select-sm mr-sm-2"
                          style="display: inline-block;width:auto"
                      >
                          <option value="10">10</option>
                          <option value="25">25</option>
                          <option value="50">50</option>
                      </select>
                      entries
                  </div>
                  <div class="col-lg-3 col-6" style="font-weight: 500">
                      Search:
                      <input
                          class="form-control form-control-sm"
                          style="display: inline-block;width:auto"
                          type="text"
                          placeholder="Search..."
                      >
                  </div>
              </div>

              <!--  create   -->
              <b-modal
                  id="modal-responsive"
                  title="Modal Content is Responsive"
                  title-class="font-18"
                  body-class="p-4"
              >
                  <div class="row">
                      <div class="col-md-6">
                          <div class="form-group">
                              <label for="field-1" class="control-label">Name</label>
                              <input
                                  type="text"
                                  class="form-control"
                                  placeholder="John"
                              />
                          </div>
                      </div>
                      <div class="col-md-6">
                          <div class="form-group">
                              <label for="field-2" class="control-label">Surname</label>
                              <input
                                  type="text"
                                  class="form-control"
                                  placeholder="Doe"
                              />
                          </div>
                      </div>
                  </div>
                  <div class="row">
                      <div class="col-md-12">
                          <div class="form-group">
                              <label for="field-3" class="control-label">Address</label>
                              <input
                                  type="text"
                                  class="form-control"
                                  placeholder="Address"
                              />
                          </div>
                      </div>
                  </div>
                  <div class="row">
                      <div class="col-md-4">
                          <div class="form-group">
                              <label for="field-4" class="control-label">City</label>
                              <input
                                  type="text"
                                  class="form-control"
                                  placeholder="Boston"
                              />
                          </div>
                      </div>
                      <div class="col-md-4">
                          <div class="form-group">
                              <label for="field-5" class="control-label">Country</label>
                              <input
                                  type="text"
                                  class="form-control"
                                  placeholder="United States"
                              />
                          </div>
                      </div>
                      <div class="col-md-4">
                          <div class="form-group">
                              <label for="field-6" class="control-label">Zip</label>
                              <input
                                  type="text"
                                  class="form-control"
                                  placeholder="123456"
                              />
                          </div>
                      </div>
                  </div>
                  <div class="row">
                      <div class="col-md-12">
                          <div class="form-group no-margin">
                              <label for="field-7" class="control-label"
                              >Personal Info</label
                              >
                              <textarea
                                  class="form-control"
                                  placeholder="Write something about yourself"
                              ></textarea>
                          </div>
                      </div>
                  </div>
              </b-modal>
              <!--  /create   -->

              <!-- start .table-responsive-->
              <div class="table-responsive mb-0 custom-table-theme">
                  <table class="table table-borderless table-hover table-centered m-0">
                      <thead>
                      <tr>
                          <th>Marketplaces</th>
                          <th>Date</th>
                          <th>US Tax Hold</th>
                          <th>Payouts</th>
                          <th>Status</th>
                          <th>Action</th>
                      </tr>
                      </thead>
                      <tbody>
                      <tr v-for="data in revenueData" :key="data.date">
                          <td>
                              <h5 class="m-0 font-weight-normal">{{ data.marketplaces }}</h5>
                          </td>
                          <td>{{ data.date }}</td>
                          <td>{{ data.tax }}</td>
                          <td>{{ data.payout }}</td>
                          <td>
                            <span :class="{
                                'bg-soft-success text-success': `${data.status}` === 'Paid',
                                'bg-soft-warning text-warning':
                                  `${data.status}` === 'Upcoming',
                                'bg-soft-danger  text-danger':
                                  `${data.status}` === 'Overdue'
                              }" class="badge">{{ data.status }}
                            </span>
                          </td>
                          <td>
                              <a href="javascript:void(0);" class="btn btn-xs btn-secondary custom-btn-1">
                                  <i class="mdi mdi-pencil"></i>
                              </a>
                              <a href="javascript:void(0);" class="btn btn-xs btn-danger custom-btn-1">
                                  <i class="fas fa-trash"></i>
                              </a>
                          </td>
                      </tr>
                      </tbody>
                  </table>
              </div>
              <!-- end .table-responsive-->

          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>
