<?php
// assessment_config.php

/**
 * Get all Movement test definitions
 */
function getMovementTests() {
    return [
        ['ex1', 'Standing Posture',
         [
            5 => '5 - Ideal alignment',
            4 => '4 - Minor forward head/pelvic tilt',
            3 => '3 - Moderate misalignment',
            2 => '2 - Compensatory postures',
            1 => '1 - Severe asymmetry',
            0 => '0 - Visible dysfunction'
         ],
        'scale5'
        ],
        ['ex2', 'Neck Flexion', [
            5 => '5 - >=50deg pain-free',
            4 => '4 - 40-49deg w/ slight stiffness',
            3 => '3 - 30-39deg or mild pain',
            2 => '2 - <30deg',
            1 => '1 - Unable or painful',
            0 => '0 - Unable or painful'
        ],
        'scale100'
        ],
        ['ex3', 'Neck Lateral Flexion', [
            5 => '5 - >=50deg pain-free',
            4 => '4 - 40-49deg w/ slight stiffness',
            3 => '3 - 30-39deg or mild pain',
            2 => '2 - <30deg',
            1 => '1 - Unable or painful',
            0 => '0 - Unable or painful'
        ],
        'scale100'
        ],
        ['ex4', 'Neck Rotation', [
            5 => '5 - >=80deg bilaterally',
            4 => '4 - 70-79deg',
            3 => '3 - 60-69deg',
            2 => '2 - 45-59deg',
            1 => '1 - <45deg or painful',
            0 => '0 - <45deg or painful'
        ],
        'scale100'
        ],
        ['ex5', 'Shoulder Flexion', [
            5 => '5 - >=170deg bilaterally',
            4 => '4 - 155-169deg',
            3 => '3 - 140-154deg',
            2 => '2 - 120-139deg',
            1 => '1 - <120deg or pain',
            0 => '0 - <120deg or pain'
        ],
        'scale100'
        ],
        ['ex6', 'Shoulder ER @ 90deg Abd', [
            5 => '5 - >=90deg both arms',
            4 => '4 - 80-89deg',
            3 => '3 - 70-79deg',
            2 => '2 - 60-69deg or asymmetry >10deg',
            1 => '1 - <60deg or pain',
            0 => '0 - <60deg or pain'
        ],
        'scale100'
        ],
        ['ex7', 'Shoulder IR @ 90deg Abd', [
            5 => '5 - >=90deg both arms',
            4 => '4 - 80-89deg',
            3 => '3 - 70-79deg',
            2 => '2 - 60-69deg or asymmetry >10deg',
            1 => '1 - <60deg or pain',
            0 => '0 - <60deg or pain'
        ],
        'scale100'
        ],
        ['ex8', 'Trunk Flexion', [
            5 => '5 - Fingers to floor',
            4 => '4 - Touch shins',
            3 => '3 - Below knees',
            2 => '2 - Mid-thigh',
            1 => '1 - Above thigh or pain',
            0 => '0 - Above thigh or pain'
        ],
        'scale5'
        ],
        ['ex9', 'Trunk Lateral Flexion', [
            5 => '5 - Fingertips to mid shin',
            4 => '4 - Knee',
            3 => '3 - Mid-thigh',
            2 => '2 - Upper-thigh',
            1 => '1 - Asymmetry or pain',
            0 => '0 - Asymmetry or pain'
        ],
        'scale5'
        ],
        ['ex10', 'Trunk Rotation', [
            5 => '5 - >=50deg each side',
            4 => '4 - 45-49deg',
            3 => '3 - 35-44deg',
            2 => '2 - 25-34deg',
            1 => '1 - <25deg or painful',
            0 => '0 - <25deg or painful'
        ],
        'scale100'
        ],
        ['ex11', 'Trunk Extension', [
            5 => '5 - Full spinal extension',
            4 => '4 - Moderate motion',
            3 => '3 - Mild pain',
            2 => '2 - Limited/painful',
            1 => '1 - Limited/painful',
            0 => '0 - Unable'
        ],
        'scale5'
        ],
        ['ex12', 'Overhead Squat', [
            5 => '5 - Dowel overhead, full depth',
            4 => '4 - Minor compensation',
            3 => '3 - Shallow depth or valgus',
            2 => '2 - Major compensation',
            1 => '1 - Unable or painful',
            0 => '0 - Unable or painful'
        ],
        'scale5'
        ],
        ['ex13', 'Lunge', [
            5 => '5 - Stable, full range',
            4 => '4 - Mild waver',
            3 => '3 - Step instability or asymmetry',
            2 => '2 - Depth limited',
            1 => '1 - Loss of balance or pain',
            0 => '0 - Loss of balance or pain'
        ],
        'scale5'
        ],
        ['ex14', 'Squat', [
            5 => '5 - Full depth, neutral spine',
            4 => '4 - Slight forward lean',
            3 => '3 - Asymmetry or shallow',
            2 => '2 - Compensation or pain',
            1 => '1 - Incomplete',
            0 => '0 - Incomplete'
        ],
        'scale5'
        ],
        ['ex15', 'Seated Hip ER', [
            5 => '5 - >=45deg both hips + <10deg asymmetry',
            4 => '4 - 40-44deg',
            3 => '3 - 30-39deg',
            2 => '2 - <30deg or asymmetry >15deg',
            1 => '1 - Pain or block',
            0 => '0 - Pain or block'
        ],
        'scale100'
        ],
        ['ex16', 'Seated Hip IR', [
            5 => '5 - >=45deg both hips + <10deg asymmetry',
            4 => '4 - 40-44deg',
            3 => '3 - 30-39deg',
            2 => '2 - <30deg or asymmetry >15deg',
            1 => '1 - Pain or block',
            0 => '0 - Pain or block'
        ],
        'scale100'
        ]
    ];
}

/**
 * Get all Grip Strength test definitions
 */
function getGripStrengthTests() {
    return [
        ['ex17', 'Grip Strength', [
            5 => '5 - 90-100th percentile',
            4 => '4 - 70-89th percentile',
            3 => '3 - 50-69th percentile',
            2 => '2 - 30-49th percentile',
            1 => '1 - 0-29th percentile',
            0 => '0 - Unable/pain limited'
        ],
        'scale100'
        ]
    ];
}

/**
 * Get all Balance and Power test definitions
 */
function getBalanceAndPowerTests() {
    return [
        ['ex18', 'Quiet Stand EO', [
            5 => '5 - Excellent', 4 => '4 - Good', 3 => '3 - Fair',
            2 => '2 - Poor', 1 => '1 - Very Poor', 0 => '0 - Unable'
        ],
        'scale5'
        ],
        ['ex19', 'Quiet Stand EC', [
            5 => '5 - Excellent', 4 => '4 - Good', 3 => '3 - Fair',
            2 => '2 - Poor', 1 => '1 - Very Poor', 0 => '0 - Unable'
        ],
        'scale5'
        ],
        ['ex20', 'SLS EO', [
            5 => '5 - Excellent', 4 => '4 - Good', 3 => '3 - Fair',
            2 => '2 - Poor', 1 => '1 - Very Poor', 0 => '0 - Unable'
        ],
        'scale5'
        ],
        ['ex21', 'SLS EC', [
            5 => '5 - Excellent', 4 => '4 - Good', 3 => '3 - Fair',
            2 => '2 - Poor', 1 => '1 - Very Poor', 0 => '0 - Unable'
        ],
        'scale5'
        ],
        ['ex22', 'CMJ', [
            5 => '5 - 90-100th percentile', 4 => '4 - 70-89th percentile',
            3 => '3 - 50-69th percentile', 2 => '2 - 30-49th percentile',
            1 => '1 - 0-29th percentile', 0 => '0 - Unable/pain limited'
        ],
        'scale100'
        ],
        ['ex23', 'SQJ', [
            5 => '5 - 90-100th percentile', 4 => '4 - 70-89th percentile',
            3 => '3 - 50-69th percentile', 2 => '2 - 30-49th percentile',
            1 => '1 - 0-29th percentile', 0 => '0 - Unable/pain limited'
        ],
        'scale100'
        ],
        ['ex24', 'SL Jump', [
            5 => '5 - Symmetry >90%, high output',
            4 => '4 - Symmetry 80-89%',
            3 => '3 - 70-79% or moderate output',
            2 => '2 - <70% or unstable',
            1 => '1 - Fall or pain',
            0 => '0 - Fall or pain'
        ],
        'scale100'
        ],
        ['ex25', 'IMTP', [
            5 => '5 - >=4.0x BW', 4 => '4 - 3.0-3.9x BW',
            3 => '3 - 2.5-2.9x BW', 2 => '2 - 2.0-2.4x BW',
            1 => '1 - <2.0x or poor form', 0 => '0 - <2.0x or poor form'
        ],
        'scale4pnt'
        ]
    ];
}
?>