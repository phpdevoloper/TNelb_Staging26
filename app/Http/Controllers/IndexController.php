<?php

namespace App\Http\Controllers;

use App\Models\Admin\Tnelb_Footerbottom;
use App\Models\Admin\Tnelb_Footercopyright;
use App\Models\Admin\Tnelb_Gallery;
use App\Models\Admin\Tnelb_homeslider_tbl;
use App\Models\Admin\Tnelb_Newsboard;
use App\Models\Admin\Tnelb_Quicklinks;
use App\Models\Admin\Tnelb_submenus;
use App\Models\Admin\Tnelb_Usefullinks;
use App\Models\Admin\Tnelb_Whatsnew;
use App\Models\Admin\TnelbMenu;
use App\Models\Admin\TnelbMenuPage;
use App\Models\Admin\Tnelb_Mst_Media;
use Carbon\Carbon;
use App\Models\Admin\Tnelb_ContactDetails;
use App\Services\Competency\CompetencyMetaService;
use Illuminate\Http\Request;

use App\Models\MstLicence;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IndexController extends Controller
{
    public function index()
    {
        $counts = [];

        foreach (CompetencyMetaService::FORM_META_TABLES as $formCode => $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $counts[$formCode] = (int) DB::table($table)
                ->whereRaw("TRIM(COALESCE(app_status, '')) = ?", ['A'])
                ->count();
        }

        // Staging/legacy DBs still store competency apps in tnelb_application_tbl.
        if ($counts === [] && Schema::hasTable('tnelb_application_tbl')) {
            $statusCol = Schema::hasColumn('tnelb_application_tbl', 'app_status')
                ? 'app_status'
                : (Schema::hasColumn('tnelb_application_tbl', 'application_status') ? 'application_status' : null);

            if ($statusCol) {
                $appCounts = DB::table('tnelb_application_tbl')
                    ->whereRaw("TRIM(COALESCE({$statusCol}, '')) = ?", ['A'])
                    ->select('form_name', DB::raw('COUNT(*) as cnt'))
                    ->groupBy('form_name')
                    ->pluck('cnt', 'form_name');

                foreach ($appCounts as $form => $cnt) {
                    $counts[strtoupper((string) $form)] = (int) $cnt;
                }
            }
        }

        if (Schema::hasTable('tnelb_ea_applications')) {
            $appCountsEA = DB::table('tnelb_ea_applications')
                ->where('application_status', 'A')
                ->select('form_name', DB::raw('COUNT(*) as cnt'))
                ->groupBy('form_name')
                ->pluck('cnt', 'form_name');

            foreach ($appCountsEA as $formEA => $cnt) {
                $counts[strtoupper((string) $formEA)] = (int) $cnt;
            }
        }

        $counts['SA'] = $counts['SA'] ?? 4;
        $counts['B'] = $counts['B'] ?? 3;
        $counts['SB'] = $counts['SB'] ?? 5;
        $counts['P'] = $counts['P'] ?? 5;

        return view('index', $this->getCommonData(), compact('counts'));
    }

    private function getCommonData()    
    {
        return [
            'sliders' => Tnelb_homeslider_tbl::with('media')->orderBy('updated_at', 'desc')->get(),
            'media' => Tnelb_Mst_Media::where([
                ['status', '=', '1'],
                ['type', '=', 'image']
            ])->orderBy('updated_at', 'desc')->get(),


            // 'sliders' => Tnelb_homeslider_tbl::where('slider_status', 1)->orderBy('updated_at')->get(),
            'menu' => TnelbMenu::with('menuPage')->where('status', 1)->orderBy('order_id')->get(),
            'submenu' => Tnelb_submenus::with('submenuPage')->where('status', 1)->orderBy('order_id')->get(),

            'whatsnew' => Tnelb_Whatsnew::whereDate('enddate', '>=', Carbon::today())->get(),
            // 'whatsnew' => Tnelb_Whatsnew::
            //     whereDate('enddate', '>=', Carbon::today())
            //     ->get(),
            // 'whatsnew' => Tnelb_Whatsnew::where('status', 1)->get(),
            // 'newsboards' => Tnelb_Newsboard::all(),

            'newsboards' => Tnelb_Newsboard::where('status', 1)
                ->whereDate('enddate', '>=', Carbon::today())
                ->get(),
            'Gallery' => Tnelb_Gallery::where('status', 1)->get(),

            'quicklinks' => Tnelb_Quicklinks::with('menuPage')->where('status', 1)->orderBy('order_id')->get(),

            'usefullinks' => Tnelb_Usefullinks::with('menuPage')->where('status', 1)->orderBy('order_id')->get(),

            'footerbottom' => Tnelb_Footerbottom::with('menuPage')->where('status', 1)->orderBy('order_id')->get(),

            'footercopyrights' => Tnelb_Footercopyright::first(),

            'contactdetails' => Tnelb_ContactDetails::all(),
            'licence_details' => MstLicence::all(),
            'aboutus' => TnelbMenuPage::where('status', 1)
                ->where('submenu_id', 1)
                ->first(),

        ];
    }
   


    public function about()
    {
        $page = TnelbMenuPage::where('status', 1)
            ->where('submenu_id', 1)
            ->first();


        if (!$page) {
            abort(404); // Page not found
        }

        return view('about', compact('page'), $this->getCommonData());
        // return view('about', $this->getCommonData());
    }

    public function vision()
    {
        $page = TnelbMenuPage::where('page_url', '/vision')
            ->where('status', 1)
            ->where('submenu_id', 3)
            ->first();


        if (!$page) {
            abort(404); // Page not found
        }
        return view('vision', compact('page'), $this->getCommonData());
    }

    public function mission()
    {
        $page = TnelbMenuPage::where('page_url', '/mission')
            ->where('status', 1)
            ->where('submenu_id', 2)
            ->first();


        if (!$page) {
            abort(404); // Page not found
        }
        return view('mission', compact('page'), $this->getCommonData());
    }


    public function future_scenario()
    {
        $page = TnelbMenuPage::where('page_url', '/future-scenario')
            ->where('status', 1)

            ->first();
        return view('future-scenario', compact('page'), $this->getCommonData());
    }

    public function members()
    {
        $page = TnelbMenuPage::where('page_url', '/members')
            ->where('status', 1)

            ->first();
        return view('members', compact('page'), $this->getCommonData());
    }

    public function rules()
    {
        $page = TnelbMenuPage::where('page_url', '/rules')
            ->where('status', 1)

            ->first();
        return view('rules', compact('page'), $this->getCommonData());
    }


    public function services_and_standards()
    {
        $page = TnelbMenuPage::where('page_url', '/services-and-standards')
            ->where('status', 1)

            ->first();
        return view('services-and-standards', compact('page'), $this->getCommonData());
    }

    public function complaints()
    {
        $page = TnelbMenuPage::where('page_url', '/complaints')
            ->where('status', 1)

            ->first();
        return view('complaints', compact('page'), $this->getCommonData());
    }

    public function contact()
    {
        $page = TnelbMenuPage::where('page_url', '/contact')
            ->where('status', 1)

            ->first();
        return view('contact', compact('page'), $this->getCommonData());
    }


    // --------------------------------------
    // ------------------------------------Footerbottom
    public function WebsitePolicies()
    {
        $page = TnelbMenuPage::where('page_url', '/WebsitePolicies')
            ->where('status', 1)

            ->first();
        return view('WebsitePolicies', compact('page'), $this->getCommonData());
    }


    public function feedback()
    {
        $page = TnelbMenuPage::where('page_url', '/feedback')
            ->where('status', 1)

            ->first();
        return view('feedback', compact('page'), $this->getCommonData());
    }

    public function help()
    {
        $page = TnelbMenuPage::where('page_url', '/help')
            ->where('status', 1)

            ->first();
        return view('help', compact('page'), $this->getCommonData());
    }


    // ------------------------

    public function login()
    {
        return view('login', $this->getCommonData());
    }

    public function validateCaptcha(Request $request)
    {
        $request->validate([
            'captcha' => 'required|captcha',
        ]);

        return back()->with('success', 'CAPTCHA matched successfully!');
    }
}
