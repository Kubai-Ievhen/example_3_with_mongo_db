<?php

namespace App\Http\Controllers;

use App\City;
use App\ComplaintFiled;
use App\Driver;
use App\Invoice;
use App\Mail\SendMail;
use App\Trip;
use App\TripCall;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class DriverController extends Controller
{
    private $data = [];

    private function getDriver($id){
        $this->data['driver'] = Driver::select('_id', 'full_name')->where('_id', $id)->first()->toArray();
    }

    /**
     * @api {get} /investor/menu Get drivers list
     * @apiName Drivers List
     * @apiGroup Drivers
     * @apiDescription Returns a page with a list of drivers
     * @apiSuccess {page} page Drivers list.
     */
    //get drivers list for menu
    public function getDrivers(){
        $drivers = Driver::select('_id', 'full_name', 'driver_status')->get()->toArray();

        return view('menu.drivers_list', ['drivers'=>$drivers]);
    }

    /**
     * @api {get} /investor/{id} Get drivers menu
     * @apiName Driver Menu
     * @apiGroup Drivers
     * @apiDescription Returns a page with a menu of driver
     * @apiParam {string} id The drivers id
     * @apiSuccess {page} page Drivers menu.
     * @apiError {page} page 404.
     */
    //get drivers data for personal menu
    public function getDriverMenu($id){
        $driver = Driver::select('_id', 'full_name', 'address', 'last_lat', 'last_lng')->where('_id', $id)->first()->toArray();
        if (count($driver) == 0){
            return view('errors.404',$this->data);
        }
        return view('menu.personal_menu', ['driver'=>$driver]);
    }

    /**
     * @api {get} /investor/booking/{id} Get drivers Booking and Missed
     * @apiName Booking and Missed
     * @apiGroup Drivers
     * @apiDescription Returns a page with a booking and missed of driver
     * @apiParam {string} id The drivers id
     * @apiSuccess {page} page Booking and Missed.
     */
    //get data for booking and missed pages
    public function getBooking($id){
        $trips = Trip::all()->toArray();
        $trip_calls = TripCall::all()->toArray();
        $this->getDriver($id);

        $misseds = [];
        $bookings = [];

        foreach ($trips as $trip){
            if(isset($trip['driver_id']) && $trip['driver_id'] == $id){
                $bookings[] = [
                                '_id'           => isset($trip['_id'])?$trip['_id']:'',
                                'driver_id'     => isset($trip['driver_id'])?$trip['driver_id']:'',
                                'trip_no'       => isset($trip['trip_no'])?$trip['trip_no']:'',
                                'created_at'    => isset($trip['created_at'])?$trip['created_at']:'',
                                'status'        => isset($trip['status'])?$trip['status']:'',
                            ];
            }
        }

        foreach ($trip_calls as $trip_call) {
            if (isset($trip_call['driver_id']) && $trip_call['driver_id'] == $id && $trip_call['status'] == "missed") {
                $trip_no = '';

                foreach ($bookings as $booking) {
                    if ($booking['_id'] == $trip_call['trip_id']) {
                        $trip_no = $booking['trip_no'];
                        break 1;
                    }
                }

                    $misseds[] = [
                        '_id' => isset($trip_call['_id']) ? $trip_call['_id'] : '',
                        'driver_id' => isset($trip_call['driver_id']) ? $trip_call['driver_id'] : '',
                        'trip_no' => $trip_no,
                        'created_at' => isset($trip_call['created_at']) ? $trip_call['created_at'] : '',
                        'status' => isset($trip_call['status']) ? $trip_call['status'] : '',
                    ];
            }
        }

        $this->data['bookings'] = $bookings;
        $this->data['misseds']  = $misseds;

        return view('menu.options.bookings', $this->data);
    }

    /**
     * @api {get} /investor/settings/{id} Get drivers profile detail list
     * @apiName Drivers Profile
     * @apiGroup Drivers
     * @apiDescription Returns a page with a profile of driver
     * @apiParam {string} id The drivers id
     * @apiSuccess {page} page Profile
     * @apiError {page} page 404.
     */
    //get driver profile
    public function getDriverProfile($id){
        $driver = Driver::where('_id', $id)->first()->toArray();
        $city = City::where('_id', $driver['city'])->first()->toArray();
        $driver['city_data'] = $city;

        if (count($driver) == 0){
            return view('errors.404',$this->data);
        }

        return view('menu.options.profile_page', ['driver'=>$driver]);
    }

    /**
     * @api {get} /investor/booking/detail/{trip_no}/{driver_id} Get details of booking
     * @apiName Booking Detail
     * @apiGroup Drivers
     * @apiDescription Returns a page with a details of booking
     * @apiParam {string} driver_id The drivers id
     * @apiParam {string} trip_no The trip number
     * @apiSuccess {page} page details of booking
     * @apiError {page} page 404.
     */
    //get booking detail
    public function getBookingDetail($trip_no, $id){
        $this->getDriver($id);

        $this->data['details'] = Invoice::where('trip_no', $trip_no)->first();
        if(count($this->data['details'])){
            $this->data['details'] = $this->data['details']->toArray();
        }

        $this->data['trip'] = Trip::where('trip_no', $trip_no)->first();
        if(count($this->data['trip'])){
            $this->data['trip'] = $this->data['trip']->toArray();
        }

        if (count($this->data['details']) == 0 && count($this->data['trip']) == 0){
            return view('errors.404',$this->data);
        }

        return view('menu.options.booking_detail',$this->data);
    }

    /**
     * @api {get} /investor/wallets/{id} Get drivers Wallets
     * @apiName Wallets
     * @apiGroup Drivers
     * @apiDescription Returns a page with a wallets of driver
     * @apiParam {string} id The drivers id
     * @apiSuccess {page} page Wallets.
     */
    // get wallet data
    public function getWallets($id){
        $wallets_all = Wallet::all()->toArray();
        $this->getDriver($id);

        $wallets_out = [];

        foreach ($wallets_all as $wallet) {
            if (isset($wallet['driver_id']) && $wallet['driver_id'] == $id) {
                $wallets_out[] = [
                    '_id' => isset($wallet['_id']) ? $wallet['_id'] : '',
                    'driver_id' => isset($wallet['driver_id']) ? $wallet['driver_id'] : '',
                    'trip_no' => isset($wallet['trip_no']) ? $wallet['trip_no'] : '',
                    'created_at' => isset($wallet['created_at']) ? $wallet['created_at'] : '',
                    'title' => isset($wallet['title']) ? $wallet['title'] : '',
                    'comments' => isset($wallet['comments']) ? $wallet['comments'] : '',
                    'balance' => isset($wallet['balance']) ? $wallet['balance'] : '',
                    'total' => isset($wallet['total']) ? $wallet['total'] : '',
                    'trip_status' => isset($wallet['trip_status']) ? $wallet['trip_status'] : '',
                    'transfer' => isset($wallet['transfer']) ? $wallet['transfer'] : '',
                    'last_received_via' => isset($wallet['last_received_via']) ? $wallet['last_received_via'] : '',
                ];
            }
        }

        $this->data['wallets'] = $wallets_out;

        return view('menu.options.wallets', $this->data);
    }

    /**
     * @api {get} /investor/complaints_filed/{id} Get drivers Complaints Filed
     * @apiName Complaints Filed
     * @apiGroup Drivers
     * @apiDescription Returns a page with a complaints filed of driver
     * @apiParam {string} id The drivers id
     * @apiSuccess {page} page Complaints Filed.
     */
    //get Complaints Filed data
    public function getComplaintsFiled($id){
        $trips = Trip::all()->toArray();
        $this->getDriver($id);

        $trips_id = [];
        foreach ($trips as $trip){
            if(isset($trip['driver_id']) && $trip['driver_id'] == $id){
                $trips_id[] =  isset($trip['trip_no'])?$trip['trip_no']:'';
            }
        }

        $complaints_fileds = ComplaintFiled::whereIn('trip_id', $trips_id)->get()->toArray();
        $this->data['complaints_fileds'] = $complaints_fileds;

        return view('menu.options.complaints_filed',$this->data);
    }

    /**
     * @api {get} /investor/statements/{id} Get drivers Statements
     * @apiName Statements
     * @apiGroup Drivers
     * @apiDescription Returns a page with a statements of driver
     * @apiParam {string} id The drivers id
     * @apiSuccess {page} page Statements.
     */
    // get Statements data
    public function getStatements($id){
        $statements_all = Trip::all()->toArray();
        $this->getDriver($id);

        $statements = [];

        foreach ($statements_all as $statement) {
            if (isset($statement['driver_id']) && $statement['driver_id'] == $id) {
                $statements[] = [
                    '_id' => isset($statement['_id']) ? $statement['_id'] : '',
                    'driver_id' => isset($statement['driver_id']) ? $statement['driver_id'] : '',
                    'trip_no' => isset($statement['trip_no']) ? $statement['trip_no'] : '',
                    'created_at' => isset($statement['created_at']) ? $statement['created_at'] : '',
                    'status' => isset($statement['status']) ? $statement['status'] : '',
                ];
            }
        }

        $this->data['statements'] = $statements;

        return view('menu.options.statements', $this->data);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getMessage($id){
        $driver = Driver::where('_id', $id)->first()->toArray();
        return view('menu.options.send_message', ['driver'=>$driver]);
    }

    /**
     * @api {post} /send_message Send Message
     * @apiName Send Message
     * @apiGroup Drivers
     * @apiDescription Send message from driver
     * @apiParam {string} driver_id The drivers id
     * @apiParam {string} content The message for send
     * @apiParam {string} value The value of parameter for change
     * @apiParam {string} name The name of parameter for change
     * @apiSuccess {back} page Back to send page.
     */
    public function sendMessage(Request $request){
        $data = $request->all();
        $data['driver'] = Driver::where('_id', $request['driver_id'])->first()->toArray();
        Mail::send(new SendMail($data));
    }

}
