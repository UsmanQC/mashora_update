<?php

namespace App\Traits;

trait Resultable
{
    public function getScore()
    {
        return $this->getResultScore($this->exam?->metric_result_type, $this->score);
    }

    /**
     * Get result score text
     *
     */
    public function getResultScore($type, $score)
    {
        $text = '';
        switch ($type) {
            case 'overall_mental_wellness':
                switch ($score) {
                    case $score >= 0 && $score <= 12:
                        $text = __('locale.low_overall_mental_well_being');
                        break;

                    case $score >= 13 && $score <= 24:
                        $text = __('locale.moderate_overall_mental_well');
                        break;

                    default:
                        $text = __('locale.high_overall_mental_well_being');
                        break;
                }
                break;

            case 'depression_metric':
                switch ($score) {
                    case $score >= 0 && $score <= 6:
                        $text = __('locale.minimal_or_no_depression');
                        break;

                    case $score >= 7 && $score <= 14:
                        $text = __('locale.mild_depression');
                        break;

                    case $score >= 15 && $score <= 22:
                        $text = __('locale.moderate_depression');
                        break;

                    case $score >= 23 && $score <= 30:
                        $text = __('locale.moderately_severe_depression');
                        break;

                    default:
                        $text = __('locale.severe_depression');
                        break;
                }
                break;

            case 'anxiety_metric':
                switch ($score) {
                    case $score >= 0 && $score <= 4:
                        $text = __('locale.minimal_or_no_anxiety');
                        break;

                    case $score >= 5 && $score <= 9:
                        $text = __('locale.mild_anxiety');
                        break;

                    case $score >= 10 && $score <= 14:
                        $text = __('locale.moderate_anxiety');
                        break;

                    case $score >= 15 && $score <= 19:
                        $text = __('locale.moderately_severe_anxiety');
                        break;

                    default:
                        $text = __('locale.severe_anxiety');
                        break;
                }
                break;

            case 'ocupational_burnout_metric':
                switch ($score) {
                    case $score >= 0 && $score <= 12:
                        $text = __('locale.low_overall_mental_well_being');
                        break;

                    case $score >= 13 && $score <= 24:
                        $text = __('locale.moderate_overall_mental_well');
                        break;

                    default:
                        $text = __('locale.high_overall_mental_well_being');
                        break;
                }
                break;

            case 'adhd_metric_kids':
                switch ($score) {
                    case $score >= 0 && $score <= 7:
                        $text = __('locale.indicates_fewer_symptoms_of_adhd');
                        break;

                    case $score >= 8 && $score <= 11:
                        $text = __('locale.suggests_mild_symptoms_of_adhd');
                        break;

                    case $score >= 12 && $score <= 14:
                        $text = __('locale.indicates_moderate_symptoms_of_adhd');
                        break;

                    default:
                        $text = __('locale.suggests_severe_symptoms_of_adhd');
                        break;
                }
                break;

            default:
                switch ($score) {
                    case $score >= 0 && $score <= 7:
                        $text = __('locale.indicates_fewer_symptoms_of_adhd');
                        break;

                    case $score >= 8 && $score <= 11:
                        $text = __('locale.suggests_mild_symptoms_of_adhd');
                        break;

                    case $score >= 12 && $score <= 14:
                        $text = __('locale.indicates_moderate_symptoms_of_adhd');
                        break;

                    default:
                        $text = __('locale.suggests_severe_symptoms_of_adhd');
                        break;
                }
                break;
        }

        return $text;
    }
}
