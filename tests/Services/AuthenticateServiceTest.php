<?php
namespace SimpleJWTLoginTests\Services;

use Exception;
use PHPUnit\Framework\TestCase;
use SimpleJWTLogin\Helpers\ServerHelper;
use SimpleJWTLogin\Modules\Settings\AuthenticationSettings;
use SimpleJWTLogin\Modules\SimpleJWTLoginHooks;
use SimpleJWTLogin\Modules\SimpleJWTLoginSettings;
use SimpleJWTLogin\Modules\WordPressDataInterface;
use SimpleJWTLogin\Services\AuthenticateService;

class AuthenticateServiceTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|WordPressDataInterface
     */
    private $wordPressDataMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->wordPressDataMock = $this
            ->getMockBuilder(WordPressDataInterface::class)
            ->getMock();
    }

    /**
     * @dataProvider validationProvider
     *
     * @param array $settings
     * @param array $request
     * @param string $expectedExceptionMessage
     *
     * @throws Exception
     */
    public function testValidation($settings,$request, $expectedExceptionMessage){
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        $this->wordPressDataMock->method('getOptionFromDatabase')
            ->willReturn(json_encode($settings));
        $authenticationService = (new AuthenticateService())
            ->withRequest($request)
            ->withCookies([])
            ->withServerHelper(new ServerHelper([]))
            ->withSettings(new SimpleJWTLoginSettings($this->wordPressDataMock));
        $authenticationService->makeAction();
    }

    /**
     * @return array[]
     */
    public function validationProvider()
    {
        return [
            [
                'settings' => [],
                'request' => [],
                'expectedMessage' => 'Authentication is not enabled.',
            ],
            [
                'settings' => [
                    'allow_authentication' => '0',
                ],
                'request' => [],
                'expectedMessage' => 'Authentication is not enabled.'
            ],
            [
                'settings' => [
                    'allow_authentication' => '1',
                ],
                'request' => [],
                'expectedMessage' => 'The email or username parameter is missing from request.'
            ],
            [
                'settings' => [
                    'allow_authentication' => '1',
                ],
                'request' => [
                    'email' => '',
                ],
                'expectedMessage' => 'The password parameter is missing from request.'
            ],
            [
                'settings' => [
                    'allow_authentication' => '1',
                ],
                'request' => [
                    'username' => '',
                ],
                'expectedMessage' => 'The password parameter is missing from request.'
            ],


        ];
    }

    public function testIpLimitation(){
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('You are not allowed to Authenticate from this IP:');
        $this->wordPressDataMock->method('getOptionFromDatabase')
            ->willReturn(json_encode(
                [
                    'allow_authentication' => 1,
                    'auth_ip' => '127.0.0.1',
                ]
            ));
        $authenticationService = (new AuthenticateService())
            ->withRequest([
                'email' => 'test@test.com',
                'password'=> '123'
            ])
            ->withCookies([])
            ->withServerHelper(new ServerHelper(['HTTP_CLIENT_IP' => '127.0.0.2']))
            ->withSettings(new SimpleJWTLoginSettings($this->wordPressDataMock));
        $authenticationService->makeAction();
    }

    public function testUserNotFoundWithEmail(){
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Wrong user credentials.');
        $this->wordPressDataMock->method('getOptionFromDatabase')
            ->willReturn(json_encode(
                 [
                     'allow_authentication' => 1,
                 ]));
        $this->wordPressDataMock->method('getUserDetailsByEmail')
            ->willReturn(null);
        $authenticationService = (new AuthenticateService())
            ->withRequest([
                              'email' => 'test@test.com',
                              'password'=> '123'
                          ])
            ->withCookies([])
            ->withServerHelper(new ServerHelper([]))
            ->withSettings(new SimpleJWTLoginSettings($this->wordPressDataMock));
        $authenticationService->makeAction();
    }

    public function testUserNotFoundWithUsername(){
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Wrong user credentials.');
        $this->wordPressDataMock->method('getOptionFromDatabase')
            ->willReturn(json_encode(
                             [
                                 'allow_authentication' => 1,
                             ]));
        $this->wordPressDataMock->method('getUserByUserLogin')
                                ->willReturn(null);
        $authenticationService = (new AuthenticateService())
            ->withRequest([
                              'username' => 'test@test.com',
                              'password'=> '123'
                          ])
            ->withCookies([])
            ->withServerHelper(new ServerHelper([]))
            ->withSettings(new SimpleJWTLoginSettings($this->wordPressDataMock));
        $authenticationService->makeAction();
    }

    public function testWrongUserCredentials()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Wrong user credentials.');

        $this->wordPressDataMock
            ->method('getOptionFromDatabase')
            ->willReturn(
                json_encode(
                    [
                        'allow_authentication' => 1,
                    ]
                )
            );
        $this->wordPressDataMock
            ->method('getUserByUserLogin')
            ->willReturn('user');
        $this->wordPressDataMock
            ->method('getUserPassword')
            ->willReturn('1234');
        $this->wordPressDataMock
            ->method('checkPassword')
            ->willReturn(false);
        $authenticationService = (new AuthenticateService())
            ->withRequest([
                              'username' => 'test@test.com',
                              'password'=> '123'
                          ])
            ->withCookies([])
            ->withServerHelper(new ServerHelper([]))
            ->withSettings(new SimpleJWTLoginSettings($this->wordPressDataMock));
        $authenticationService
            ->makeAction();
    }

    public function testSuccessFlowWithFullPayload(){
        $this->wordPressDataMock
            ->method('getOptionFromDatabase')
            ->willReturn(
                json_encode(
                    [
                        'allow_authentication' => 1,
                        'jwt_payload' => [
                            AuthenticationSettings::JWT_PAYLOAD_PARAM_IAT,
                            AuthenticationSettings::JWT_PAYLOAD_PARAM_EMAIL,
                            AuthenticationSettings::JWT_PAYLOAD_PARAM_EXP,
                            AuthenticationSettings::JWT_PAYLOAD_PARAM_ID,
                            AuthenticationSettings::JWT_PAYLOAD_PARAM_SITE,
                            AuthenticationSettings::JWT_PAYLOAD_PARAM_USERNAME
                        ],
                        'enabled_hooks' => [
                            SimpleJWTLoginHooks::JWT_PAYLOAD_ACTION_NAME
                        ]
                    ]
                )
            );
        $this->wordPressDataMock
            ->method('getUserByUserLogin')
            ->willReturn('user');
        $this->wordPressDataMock
            ->method('getUserPassword')
            ->willReturn('1234');
        $this->wordPressDataMock
            ->method('checkPassword')
            ->willReturn(true);
        $this->wordPressDataMock
            ->method('createResponse')
            ->willReturn(true);
        $authenticationService = (new AuthenticateService())
            ->withRequest([
                              'username' => 'test@test.com',
                              'password'=> '123'
                          ])
            ->withCookies([])
            ->withServerHelper(new ServerHelper([]))
            ->withSettings(new SimpleJWTLoginSettings($this->wordPressDataMock));
        $result = $authenticationService
            ->makeAction();
        $this->assertTrue($result);
    }
}