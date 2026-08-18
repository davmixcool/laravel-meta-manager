{{--
    The Blade entry point.

    Applications include this view directly:

        @include('meta::manager', ['title' => '...', 'description' => '...'])

    It delegates to the render engine's extended profile, which prints the full
    v1 tag set and adds only non-conflicting tags on top. Nothing in an existing
    application needs to change.

    get_defined_vars() is used on purpose: Blade shares the parent view's scope
    with @include, and v1 resolved its variables from that same shared scope.
    MetaManager filters it down to the ten documented include variables.

    The original v1 template is preserved verbatim at
    tests/fixtures/v1/manager.blade.php and is diffed against this output by
    the compatibility test suite.
--}}
{!! \Davmixcool\MetaManager\Facades\Meta::blade(get_defined_vars()) !!}
