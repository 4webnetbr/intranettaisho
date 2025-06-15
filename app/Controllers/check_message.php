<?php
header('Content-Type: application/json');

echo json_encode(['msg' => session()->getFlashdata('msgimp')]);
