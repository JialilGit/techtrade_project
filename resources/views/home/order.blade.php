<!DOCTYPE html>
<html>
   <head>
    

     <link data-require="sweet-alert@*" data-semver="0.4.2" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
  <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>


    @include('home.css')

      <style type="text/css">
         

         .center
         {

            margin: auto;
            width: 70%;
            padding: 30px;
            text-align: center;
         }


         table,th,td
         {
            border: 1px solid black;
         }

         .th_deg

         {

            padding: 10px;
            background-color: rgba(69,119,12,255);
            font-size: 20px;
            font-weight: bold;
            color: white;

         }

         .processing-status {
            color: orange;
         }

         .delivered-status {
            color: green;
         }
      </style>


   </head>
   <body>

      @include('sweetalert::alert')
   <div class="hero_area">
         <!-- header section strats -->
       @include('home.header')
         

         <div class="center" style="overflow-x:auto;">
            

            <table>
               
               <tr>
                  <th class="th_deg">Order ID</th>

                  <th class="th_deg">Image</th>

                  <th class="th_deg">Product Title</th>

                  <th class="th_deg">Quantity</th>

                  <th class="th_deg">Total Price</th>

                  <th class="th_deg">Payment Status</th>

                  <th class="th_deg">Delivery Status</th>

                  <th class="th_deg">Cancel Order</th>

               </tr>


               @foreach($order as $order)
               <tr>
                  <td>{{ str_pad($order->id, 8, '0', STR_PAD_LEFT) }}</td>

                  <td>
                     
                     <img height="200" width="180" src="product/{{$order->image}}">

                  </td>

                  <td>{{$order->product_title}}</td>

                  <td>{{$order->quantity}}</td>

                  <td>₱{{number_format($order->price, 2)}}</td>

                  <td>{{$order->payment_status}}</td>

                  <td style="color: {{ $order->delivery_status === 'Processing' ? 'orange' : ($order->delivery_status === 'Delivered' ? 'green' : 'red') }}">
                        {{ $order->delivery_status }}
                  </td>

 
                  <td>

                     @if($order->delivery_status=='Processing')

                     <a onclick="confirmation(event)" class="btn btn-danger" href="{{url('cancel_order',$order->id)}}">Cancel Order</a>


                     @else

                     
                     <a onclick="confirmation(event)" class="btn btn-danger{{ $order->delivery_status === 'Order Cancelled' ? ' disabled' : '' }}" href="{{url('cancel_order',$order->id)}}">Cancel Order</a>


                     @endif

                  </td>

               </tr>

               @endforeach


            </table>

         </div>
      


            
   



<script>
      function confirmation(ev) {
        ev.preventDefault();
        var urlToRedirect = ev.currentTarget.getAttribute('href'); //use currentTarget because the click may be on the nested i tag and not a tag causing the href to be empty
        console.log(urlToRedirect); // verify if this is the right URL
        swal({
            title: "Are you sure to cancel this product",
            text: "You will not be able to revert this!",
            icon: "warning",
            buttons: true, 
            dangerMode: true,
        })
        .then((willCancel) => {
            if (willCancel) {


                 
                window.location.href = urlToRedirect;
               
            }  
        });
    }
</script>
      
      <!-- jQery -->
           <!-- jQery -->
      <script src="home/js/jquery-3.4.1.min.js"></script>
      <!-- popper js -->
      <script src="home/js/popper.min.js"></script>
      <!-- bootstrap js -->
      <script src="home/js/bootstrap.js"></script>
      <!-- custom js -->
      <script src="home/js/custom.js"></script>
   </body>
</html>