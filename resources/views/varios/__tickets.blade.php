@extends('profile.main')

@section('p-content')
    <section class="col-lg-12">
        <!-- Toolbar-->
        {{-- <div class="d-flex justify-content-between align-items-center pt-lg-2 pb-4 pb-lg-5 mb-lg-3">
      <div class="d-flex align-items-center">
        <label class="d-none d-lg-block fs-sm text-light text-nowrap opacity-75 me-2" for="order-sort">Sort orders:</label>
        <label class="d-lg-none fs-sm text-nowrap opacity-75 me-2" for="order-sort">Sort orders:</label>
        <select class="form-select" id="order-sort">
          <option>All</option>
          <option>Delivered</option>
          <option>In Progress</option>
          <option>Delayed</option>
          <option>Canceled</option>
        </select>
      </div><a class="btn btn-primary btn-sm d-none d-lg-inline-block" href="account-signin.html"><i class="ci-sign-out me-2"></i>Sign out</a>
    </div> --}}
        <!-- Orders list-->
        <!-- Light table with striped rows -->
        <div class="table-responsive fs-md mb-4">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date Purchased</th>
                        <th>Payment</th>
                        <th>Shipping</th>
                        <th>Status</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tickets as $item)
                        <tr>
                         <td class="py-3"><a class="nav-link-style fw-medium fs-sm" href="#order-details"
                                    data-bs-toggle="modal">{{ $item->id }}</a></td>
                            <td class="py-3">{{ $item->created_at }}</td>
                         {{--       <td class="py-3">{{ $item->forma_pago_desc }}</td>
                            <td class="py-3">{{ $item->tipo_envio_desc }}</td>
                            <td class="py-3"><span class="badge bg-info badge-shadow m-0">{{ $item->oc_estado }}</span></td>
                            <td class="py-3">${{ number_format($item->total, 2) }}</td> --}}
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{-- {!! $tickets->links() !!} --}}
        </div>

    </section>
@endsection
