<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WellSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('wells')->insert([
            [
                'company_id' => 1,
                'site_id' => 1,
                'lease' => 'Silverton 1H',
                'lift_method' => 'Gas Lift',
                'chemical' => 'EFSC 6725X-GL',
                'chemical_type' => 'Cor./Scale/Fes Inh.',
                'rate' => 125,
                'based_on' => 'Water',
                'injection_point' => 'Gas Lift',
                'comments' => null
            ],
            [
                'company_id' => 1,
                'site_id' => 1,
                'lease' => 'Silverton 2H',
                'lift_method' => 'Gas Lift',
                'chemical' => 'EFSC 6725X-GL',
                'chemical_type' => 'Cor./Scale/Fes Inh.',
                'rate' => 125,
                'based_on' => 'Water',
                'injection_point' => 'Gas Lift',
                'comments' => null
            ],
            [
                'company_id' => 1,
                'site_id' => 1,
                'lease' => 'Silverton 3H',
                'lift_method' => 'Gas Lift',
                'chemical' => 'EFSC 6725X-GL',
                'chemical_type' => 'Cor./Scale/Fes Inh.',
                'rate' => 125,
                'based_on' => 'Water',
                'injection_point' => 'Gas Lift',
                'comments' => null
            ],
            [
                'company_id' => 1,
                'site_id' => 1,
                'lease' => 'Silverton 4H',
                'lift_method' => 'Gas Lift',
                'chemical' => 'EFSC 6725X-GL',
                'chemical_type' => 'Cor./Scale/Fes Inh.',
                'rate' => 125,
                'based_on' => 'Water',
                'injection_point' => 'Gas Lift',
                'comments' => null
            ],
            [
                'company_id' => 1,
                'site_id' => 1,
                'lease' => 'Silverton 5H',
                'lift_method' => 'ESP',
                'chemical' => 'EFSC 2362',
                'chemical_type' => 'Cor./Scale/Fes Inh.',
                'rate' => 70,
                'based_on' => 'Water',
                'injection_point' => 'Cap String',
                'comments' => null
            ],
            [
                'company_id' => 1,
                'site_id' => 1,
                'lease' => 'Silverton 6H',
                'lift_method' => 'ESP',
                'chemical' => 'EFSC 2362',
                'chemical_type' => 'Cor./Scale/Fes Inh.',
                'rate' => 70,
                'based_on' => 'Water',
                'injection_point' => 'Cap String',
                'comments' => null
            ],
            [
                'company_id' => 1,
                'site_id' => 1,
                'lease' => 'Silverton 7H',
                'lift_method' => 'ESP',
                'chemical' => 'EFSC 2362',
                'chemical_type' => 'Cor./Scale/Fes Inh.',
                'rate' => 70,
                'based_on' => 'Water',
                'injection_point' => 'Cap String',
                'comments' => null
            ],
            [
                'company_id' => 1,
                'site_id' => 1,
                'lease' => 'Silverton 101H',
                'lift_method' => 'Gas Lift',
                'chemical' => 'EFSC 6725X-GL',
                'chemical_type' => 'Cor./Scale/Fes Inh.',
                'rate' => 125,
                'based_on' => 'Water',
                'injection_point' => 'Gas Lift',
                'comments' => null
            ],
            [
                'company_id' => 1,
                'site_id' => 1,
                'lease' => 'Silverton CTB Produced H2O',
                'lift_method' => 'Water Tanks',
                'chemical' => 'EFAI 9005',
                'chemical_type' => 'Fes/Biomass Control',
                'rate' => 50,
                'based_on' => 'Water',
                'injection_point' => 'Produced Water',
                'comments' => null
            ],
            [
                'company_id' => 1,
                'site_id' => 1,
                'lease' => 'Silverton CTB Gas P/L',
                'lift_method' => 'Gas Sales',
                'chemical' => 'EFHS 8344',
                'chemical_type' => 'H2S Scavenger',
                'rate' => null,
                'based_on' => 'Gas',
                'injection_point' => 'Gas Sales P/L',
                'comments' => null
            ],
        ]);
    }
}
