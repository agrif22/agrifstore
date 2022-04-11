@extends('layouts.dashboard')

@section('title')
    Store Dashboard Transaction Detail
@endsection



@section('content')
<!-- Section Content -->
          <div
            class="section-content section-dashboard-home"
            data-aos="fade-up"
          >
            <div class="container-fluid">
              <div class="dashboard-heading">
                <h2 class="dashboard-title">{{ $transaction->code}}</h2>
                <p class="dashboard-subtitle">Transaction Details</p>
              </div>
              <div class="dashboard-contain" id="transactionDetails">
                <div class="row">
                  <div class="col-12">
                    <div class="card">
                      <div class="card-body">
                        <div class="row">
                          <div class="col-12 col-md-4">
                            <img
                              src="{{ Storage::url($transaction->product->galleries->first()->photos ?? '')}}"
                              class="w-100 mb-3"
                              alt=""
                            />
                          </div>
                          <div class="col-12 col-md-8">
                            <div class="row">
                              <div class="col-12 col-md-6">
                                <div class="product-title">Customer Name</div>
                                <div class="product-subtitle">{{$transaction->transaction->name}}</div>
                              </div>
                              <div class="col-12 col-md-6">
                                <div class="product-title">Product Name</div>
                                <div class="product-subtitle">{{$transaction->product->name}}</div>
                              </div>
                              <div class="col-12 col-md-6">
                                <div class="product-title">
                                  Date Of Transaction
                                </div>
                                <div class="product-subtitle">
                                  {{$transaction->created_at}}
                                </div>
                              </div>
                              <div class="col-12 col-md-6">
                                <div class="product-title">Payment Status</div>
                                <div class="product-subtitle text-danger">
                                  {{$transaction->transaction->transaction_status}}
                                </div>
                              </div>
                              <div class="col-12 col-md-6">
                                <div class="product-title">Total Amount</div>
                                <div class="product-subtitle">Rp. {{$transaction->transaction->total_price}}</div>
                              </div>
                              <div class="col-12 col-md-6">
                                <div class="product-title">Mobile</div>
                                <div class="product-subtitle">
                                  {{$transaction->transaction->user->phone_number}}
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        <form action="{{ route('dashboard-transaction-update', $transaction->id)}}" enctype="multipart/form-data" method="POST">
                          @csrf
                            <div class="row">
                              <div class="col-12 mt-4">
                                <h5>Shipping Infomation</h5>
                              </div>
                              <div class="col-12">
                                <div class="row">
                                  <div class="col-12 col-md-6">
                                    <div class="product-title">Address I</div>
                                    <div class="product-subtitle">
                                      {{$transaction->transaction->user->address_one}}
                                    </div>
                                  </div>
                                  <div class="col-12 col-md-6">
                                    <div class="product-title">Address II</div>
                                    <div class="product-subtitle">
                                      {{$transaction->transaction->user->address_two}}
                                    </div>
                                  </div>
                                  <div class="col-12 col-md-6">
                                    <div class="product-title">Province</div>
                                    <div class="product-subtitle">
                                      {{ App\Models\Province::find($transaction->transaction->user->provinces_id)->name}}
                                    </div>
                                  </div>
                                  <div class="col-12 col-md-6">
                                    <div class="product-title">City</div>
                                    <div class="product-subtitle">
                                      {{ App\Models\Regency::find($transaction->transaction->user->regencies_id)->name}}
                                    </div>
                                  </div>
                                  <div class="col-12 col-md-6">
                                    <div class="product-title">Postal Code</div>
                                    <div class="product-subtitle">
                                      {{$transaction->transaction->user->zip_code}}
                                    </div>
                                  </div>
                                  <div class="col-12 col-md-6">
                                    <div class="product-title">Country</div>
                                    <div class="product-subtitle">
                                      {{$transaction->transaction->user->country}}
                                    </div>
                                  </div>
                                  <div class="col-12 col-md-3">
                                    <div class="product-title">Shipping Status</div>
                                    <select
                                      name="shipping_status"
                                      id="status"
                                      class="form-control"
                                      v-model="status"
                                    >
                                      <option value="PENDING">Pending</option>
                                      <option value="SHIPPING">Shipping</option>
                                      <option value="SUCCESS">Success</option>
                                    </select>
                                  </div>
                                  <template v-if="status == 'SHIPPING'">
                                    <div class="col-md-6">
                                      <div class="product-title">Input Resi</div>
                                      <input
                                        type="text"
                                        class="form-control"
                                        name="resi"
                                        v-model="resi"
                                      />
                                    </div>
                                    <div class="col-md-3">
                                      <button
                                        type="submit"
                                        class="btn btn-success btn-block mt-3"
                                      >
                                        Update Resi
                                      </button>
                                    </div>
                                  </template>
                                </div>
                              </div>
                            </div>
                            <div class="row mt-4">
                              <div class="col-md-12 text-right">
                                <button
                                  type="submit"
                                  class="btn btn-success btn-lg mt-4"
                                >
                                  Save Now
                                </button>
                              </div>
                            </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /Section Content -->
@endsection

@push('addon-script')
    <script src="/vendor/vue/vue.js"></script>
    <script>
      var transactionDetails = new Vue({
        el: "#transactionDetails",
        data: {
          status: "{{$transaction->shipping_status}}",
          resi: "{{$transaction->resi}}",
        },
      });
    </script>
@endpush