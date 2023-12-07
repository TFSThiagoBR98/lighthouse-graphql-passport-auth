<?php

declare(strict_types=1);

namespace TFSThiagoBR98\LighthouseGraphQLPassport\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use TFSThiagoBR98\LighthouseGraphQLPassport\Contracts\AuthModelFactory as AuthModelFactoryContract;
use TFSThiagoBR98\LighthouseGraphQLPassport\Factories\AuthModelFactory;
use TFSThiagoBR98\LighthouseGraphQLPassport\OAuthGrants\LoggedInGrant;
use TFSThiagoBR98\LighthouseGraphQLPassport\OAuthGrants\SocialGrant;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Bridge\UserRepository;
use Laravel\Passport\Passport;
use League\OAuth2\Server\AuthorizationServer;
use Nuwave\Lighthouse\Events\BuildSchemaString;

/**
 * Class LighthouseGraphQLPassportServiceProvider.
 */
class LighthouseGraphQLPassportServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        if (config('lighthouse-graphql-passport.migrations')) {
            $this->loadMigrationsFrom(__DIR__.'/../../migrations');
        }

        Validator::extend('full_name', function($attribute, $value)
        {
            if (! is_string($value) && ! is_numeric($value)) {
                return false;
            }
    
            return preg_match('/^[^(\|\]~`!%#¨^&*=\$\@};:+\"\”\“\/\[\\\\\{\}?><’)]*$/u', $value) > 0;
        });
    }

    public function register(): void
    {
        $this->app->singleton(AuthModelFactoryContract::class, AuthModelFactory::class);

        $this->extendAuthorizationServer();
        $this->registerConfig();

        $lightHouseDirectives = Arr::wrap(config('lighthouse.namespaces.directives', []));
        $authDirective = 'Nuwave\\Lighthouse\\Auth';

        if (! in_array($authDirective, $lightHouseDirectives)) {
            $lightHouseDirectives[] = $authDirective;
            config()->set([
                'lighthouse.namespaces.directives' => $lightHouseDirectives,
            ]);
        }
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/config.php',
            'lighthouse-graphql-passport'
        );

        $this->publishes([
            __DIR__.'/../../config/config.php' => config_path('lighthouse-graphql-passport.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../../graphql/auth.graphql' => base_path('graphql/auth.graphql'),
        ], 'schema');

        $this->publishes([
            __DIR__.'/../../migrations/2019_11_19_000000_update_social_provider_users_table.php' => base_path('database/migrations/2019_11_19_000000_update_social_provider_users_table.php'),
        ], 'migrations');
    }

    /**
     * @return SocialGrant
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function makeCustomRequestGrant(): SocialGrant
    {
        $grant = new SocialGrant(
            $this->app->make(UserRepository::class),
            $this->app->make(RefreshTokenRepository::class)
        );
        $grant->setRefreshTokenTTL(Passport::refreshTokensExpireIn());

        return $grant;
    }

    /**
     * @return LoggedInGrant
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function makeLoggedInRequestGrant(): LoggedInGrant
    {
        $grant = new LoggedInGrant(
            $this->app->make(UserRepository::class),
            $this->app->make(RefreshTokenRepository::class)
        );
        $grant->setRefreshTokenTTL(Passport::refreshTokensExpireIn());

        return $grant;
    }

    /**
     * Register the Grants.
     *
     * @return void
     */
    protected function extendAuthorizationServer(): void
    {
        $this->app->extend(AuthorizationServer::class, function ($server) {
            return tap($server, function ($server) {
                $server->enableGrantType(
                    $this->makeLoggedInRequestGrant(),
                    Passport::tokensExpireIn()
                );

                $server->enableGrantType(
                    $this->makeCustomRequestGrant(),
                    Passport::tokensExpireIn()
                );
            });
        });
    }
}
