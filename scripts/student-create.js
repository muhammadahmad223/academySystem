 
        document.addEventListener('DOMContentLoaded', () => {

            const form = document.getElementById('wizard-form');
            const steps = form.querySelectorAll('.wizard-step');
            const indicators = document.querySelectorAll('#wizard-steps li');

            const prevBtn = document.getElementById('wz-prev');
            const nextBtn = document.getElementById('wz-next');
            const submitBtn = document.getElementById('wz-submit');
            const academicFeesContent = document.getElementById('academic-fees-content');
            const successMessage = document.getElementById('save-success');
            const addNewStudentBtn = document.getElementById('add-new-student');

            const params = new URLSearchParams(window.location.search);

            const validateStep = (step) => {

                const fields = step.querySelectorAll('.required-field');

                let valid = true;

                fields.forEach((field) => {

                    if (field.value.trim() === '') {

                        field.classList.add('is-invalid');

                        valid = false;

                    } else {

                        field.classList.remove('is-invalid');

                    }

                });

                return valid;
            };

            let current = parseInt(params.get('step')) || 0;

            const showStep = (index) => {

                steps.forEach((step, i) => {

                    step.classList.toggle('d-none', i !== index);

                });


                indicators.forEach((indicator, i) => {

                    indicator.classList.toggle('active', i === index);
                    indicator.classList.toggle('completed', i < index);

                });


                // Previous
                prevBtn.disabled = index === 0;

                // Next / Save
                const lastStep = index === steps.length - 1;
                nextBtn.classList.toggle('d-none', lastStep);

                submitBtn.classList.toggle('d-none', !lastStep);

            };



            nextBtn.addEventListener('click', () => {

                const currentStep = steps[current];

                if (!validateStep(currentStep)) {
                    return;
                }

                if (current < steps.length - 1) {
                    current++;
                    showStep(current);

                    const url = new URL(window.location);
                    url.searchParams.set('step', current);
                    window.history.pushState({}, '', url);
                }

            });

            prevBtn.addEventListener('click', () => {

                if (current > 0) {
                    current--;
                    showStep(current);

                    // Update URL
                    const url = new URL(window.location);
                    url.searchParams.set('step', current);
                    window.history.pushState({}, '', url);

                }

            });

            form.addEventListener('keydown', (e) => {

                if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                }

            });

            form.addEventListener('submit', (e) => {

                const currentStep = steps[current];

                if (!validateStep(currentStep)) {
                    e.preventDefault();
                    return;
                }

            });

            addNewStudentBtn.addEventListener('click', () => {

                window.location.href = 'create.php';

            });


            if (params.get('save') === 'success') {

                // Step 2 show karo
                current = 2;
                showStep(current);

                // All 3 steps completed
                indicators.forEach((indicator) => {
                    indicator.classList.remove('active');
                    indicator.classList.add('completed');
                });

                // Academic & Fees fields hide
                academicFeesContent.classList.add('d-none');

                // Success message show
                successMessage.classList.remove('d-none');

                // All buttons hide
                prevBtn.classList.add('d-none');
                submitBtn.classList.add('d-none');
                nextBtn.classList.add('d-none');

            } else {

                // Normal wizard
                showStep(current);

            }
        });
 