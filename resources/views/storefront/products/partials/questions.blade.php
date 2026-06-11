@php
    $approvedQuestions = $product->approvedQuestions ?? collect();

    $questionsCount = $approvedQuestions->count();

    $userName = auth()->user()?->name ?? '';
    $userEmail = auth()->user()?->email ?? '';
@endphp

<section class="scp-product-questions-section" id="questions">
    <div class="scp-product-questions-head">
        <div>
            <span class="scp-product-questions-badge">
                {{ __('storefront.questions.badge') }}
            </span>

            <h2>{{ __('storefront.questions.title') }}</h2>

            <p>{{ __('storefront.questions.subtitle') }}</p>
        </div>

        <div class="scp-product-questions-count">
            <strong>{{ $questionsCount }}</strong>
            <span>{{ __('storefront.questions.questions_count') }}</span>
        </div>
    </div>

    <div class="scp-product-questions-grid">
        <div class="scp-product-question-form-card">
            <h3>{{ __('storefront.questions.ask_question') }}</h3>

            <p>{{ __('storefront.questions.approval_notice') }}</p>

            <form
                method="POST"
                action="{{ route('storefront.products.questions.store', ['product' => $product->id, 'lang' => $locale]) }}"
                class="scp-product-question-form"
            >
                @csrf

                <input type="hidden" name="lang" value="{{ $locale }}">

                <div class="scp-question-field">
                    <label>{{ __('storefront.questions.name') }}</label>
                    <input
                        type="text"
                        name="customer_name"
                        value="{{ old('customer_name', $userName) }}"
                        required
                        maxlength="120"
                    >
                </div>

                <div class="scp-question-field">
                    <label>{{ __('storefront.questions.email') }}</label>
                    <input
                        type="email"
                        name="customer_email"
                        value="{{ old('customer_email', $userEmail) }}"
                        maxlength="180"
                    >
                </div>

                <div class="scp-question-field">
                    <label>{{ __('storefront.questions.question') }}</label>
                    <textarea
                        name="question"
                        rows="5"
                        required
                        minlength="5"
                        maxlength="1500"
                        placeholder="{{ __('storefront.questions.question_placeholder') }}"
                    >{{ old('question') }}</textarea>
                </div>

                <button type="submit" class="scp-question-submit-btn">
                    {{ __('storefront.questions.submit') }}
                </button>
            </form>
        </div>

        <div class="scp-product-approved-questions-card">
            <div class="scp-product-approved-questions-head">
                <h3>{{ __('storefront.questions.approved_questions') }}</h3>
                <span>{{ $questionsCount }}</span>
            </div>

            @if($questionsCount > 0)
                <div class="scp-approved-questions-list">
                    @foreach($approvedQuestions->take(10) as $question)
                        <article class="scp-approved-question-item">
                            <div class="scp-approved-question-top">
                                <div class="scp-question-avatar">
                                    {{ mb_substr($question->customer_name, 0, 1) }}
                                </div>

                                <div>
                                    <strong>{{ $question->customer_name }}</strong>
                                    <small>{{ optional($question->approved_at ?? $question->created_at)->format('Y-m-d') }}</small>
                                </div>
                            </div>

                            <div class="scp-question-text">
                                <span>{{ __('storefront.questions.question_label') }}</span>
                                <p>{{ $question->question }}</p>
                            </div>

                            @if($question->answer)
                                <div class="scp-answer-text">
                                    <span>{{ __('storefront.questions.answer_label') }}</span>
                                    <p>{{ $question->answer }}</p>
                                </div>
                            @else
                                <div class="scp-answer-pending">
                                    {{ __('storefront.questions.answer_pending') }}
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @else
                <div class="scp-questions-empty">
                    <div>?</div>
                    <h4>{{ __('storefront.questions.empty_title') }}</h4>
                    <p>{{ __('storefront.questions.empty_text') }}</p>
                </div>
            @endif
        </div>
    </div>
</section>