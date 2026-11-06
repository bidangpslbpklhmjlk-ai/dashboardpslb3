<?php
dump(DB::table('rute_angkut_lokasi')->whereNotNull('pekan')->get()->toArray());
