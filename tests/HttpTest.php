<?php

declare(strict_types=1);

namespace Osirisgate\Component\HttpClient\Tests;

use Osirisgate\Component\HttpClient\Request\Http;
use Osirisgate\Core\Enum\Status;
use Osirisgate\Core\Enum\StatusCode;
use Osirisgate\Core\Exception\ExceptionInterface;
use PHPUnit\Framework\TestCase;

final class HttpTest extends TestCase
{
    private const string BASE_URL = 'https://jsonplaceholder.typicode.com';

    public function testGetPosts(): void
    {
        try {
            $response = Http::get(self::BASE_URL . '/posts');
            $responseData = $response->toArray();

            self::assertEquals(StatusCode::OK->getValue(), $response->getStatusCode());
            self::assertGreaterThan(0, count($responseData), 'No posts found!');
        } catch (ExceptionInterface $e) {
            self::assertEquals(Status::ERROR->getValue(), $e->getCode());
        }
    }

    public function testGetPostById(): void
    {
        try {
            $response = Http::get(self::BASE_URL . '/posts/1');
            self::assertEquals(StatusCode::OK->getValue(), $response->getStatusCode());

            $post = $response->toArray();
            self::assertEquals(1, $post['id'], 'Post ID should be 1');
        } catch (ExceptionInterface $e) {
            self::assertEquals(Status::ERROR->getValue(), $e->getCode());
        }
    }

    public function testGetCommentsByPostId(): void
    {
        try {
            $response = Http::get(self::BASE_URL . '/comments', [], [
                'postId' => 1,
            ]);
            self::assertEquals(StatusCode::OK->getValue(), $response->getStatusCode());
            $comments = $response->toArray();
            foreach ($comments as $comment) {
                self::assertEquals(1, $comment['postId'], 'Invalid postId in comment');
            }
        } catch (ExceptionInterface $e) {
            self::assertEquals(Status::ERROR->getValue(), $e->getCode());
        }
    }

    public function testGetCommentsForNonExistentPostId(): void
    {
        try {
            $response = Http::get(self::BASE_URL . '/comments', [], [
                'postId' => 9999,
            ]);
            self::assertEquals(StatusCode::OK->getValue(), $response->getStatusCode());

            $comments = $response->toArray();
            $this->assertEmpty($comments, 'Expected an empty array for non-existent postId');
        } catch (ExceptionInterface $e) {
            self::assertEquals(Status::ERROR->getValue(), $e->getCode());
        }
    }

    public function testGetUserById(): void
    {
        try {
            $response = Http::get(self::BASE_URL . '/users/1');
            self::assertEquals(StatusCode::OK->getValue(), $response->getStatusCode());

            $user = $response->toArray();
            self::assertEquals(1, $user['id'], 'User ID should be 1');
        } catch (ExceptionInterface $e) {
            self::assertEquals(Status::ERROR->getValue(), $e->getCode());
        }
    }

    public function testGetUsers(): void
    {
        try {
            $response = Http::get(self::BASE_URL . '/users');
            self::assertEquals(StatusCode::OK->getValue(), $response->getStatusCode());
            self::assertNotEmpty($response->getContent());

            $users = $response->toArray();
            $this->assertGreaterThan(0, count($users), 'No users found!');
        } catch (ExceptionInterface $e) {
            self::assertEquals(Status::ERROR->getValue(), $e->getCode());
        }
    }

    public function testGetAlbumsByUserId(): void
    {
        try {
            $response = Http::get(self::BASE_URL . '/albums', [], [
                'userId' => 1,
            ]);
            self::assertEquals(StatusCode::OK->getValue(), $response->getStatusCode());
            $this->assertNotEmpty($response->getContent());

            $albums = $response->toArray();
            foreach ($albums as $album) {
                self::assertEquals(1, $album['userId'], 'Invalid userId in album');
            }
        } catch (ExceptionInterface $e) {
            self::assertEquals(Status::ERROR->getValue(), $e->getCode());
        }
    }

    public function testInvalidUrlThrowsHttpException(): void
    {
        try {
            $response = Http::get(self::BASE_URL . '/invalid-endpoint');
        } catch (ExceptionInterface $e) {
            self::assertEquals(404, $e->getCode(), 'Expected HTTP status code 404 for invalid endpoint');
        }
    }

    public function testResponseHeaders(): void
    {
        try {
            $response = Http::get(self::BASE_URL . '/posts');
            self::assertEquals(StatusCode::OK->getValue(), $response->getStatusCode());
            $headers = $response->getHeaders();

            $this->assertArrayHasKey('content-type', $headers);
            $this->assertStringContainsString('application/json', $headers['content-type'][0]);
        } catch (ExceptionInterface $e) {
            self::assertEquals(Status::ERROR->getValue(), $e->getCode());
        }
    }

    public function testClientError404(): void
    {
        try {
            Http::get(self::BASE_URL . '/posts/99999');
        } catch (ExceptionInterface $e) {
            self::assertEquals(StatusCode::NOT_FOUND->getValue(), $e->getCode());
        }
    }

    public function testPostCreateNewPost(): void
    {
        try {
            $payload = [
                'title' => 'foo',
                'body' => 'bar',
                'userId' => 1,
            ];

            $response = Http::post(self::BASE_URL . '/posts', [], $payload);
            self::assertEquals(StatusCode::CREATED->getValue(), $response->getStatusCode());

            $data = $response->toArray();

            $this->assertEquals('foo', $data['title']);
            $this->assertEquals('bar', $data['body']);
            $this->assertEquals(1, $data['userId']);
            $this->assertArrayHasKey('id', $data);
        } catch (ExceptionInterface $e) {
            self::assertEquals(StatusCode::INTERNAL_SERVER_ERROR->getValue(), $e->getCode());
        }
    }

    public function testPutUpdatePost(): void
    {
        try {
            $payload = [
                'id' => 1,
                'title' => 'updated title',
                'body' => 'updated body',
                'userId' => 1,
            ];

            $response = Http::put(self::BASE_URL . '/posts/1', [], $payload);
            self::assertEquals(StatusCode::OK->getValue(), $response->getStatusCode());

            $data = $response->toArray();
            $this->assertEquals('updated title', $data['title']);
            $this->assertEquals('updated body', $data['body']);
        } catch (ExceptionInterface $e) {
            self::assertEquals(StatusCode::INTERNAL_SERVER_ERROR->getValue(), $e->getCode());
        }
    }

    public function testPatchUpdatePost(): void
    {
        try {
            $payload = [
                'title' => 'patched title',
            ];

            $response = Http::patch(self::BASE_URL . '/posts/1', [], $payload);
            self::assertEquals(StatusCode::OK->getValue(), $response->getStatusCode());

            $this->assertEquals('patched title', $response->get('title'));
        } catch (ExceptionInterface $e) {
            self::assertEquals(StatusCode::INTERNAL_SERVER_ERROR->getValue(), $e->getCode());
        }
    }

    public function testDeletePost(): void
    {
        try {
            $response = Http::delete(self::BASE_URL . '/posts/1');
            self::assertEquals(StatusCode::OK->getValue(), $response->getStatusCode());

            $this->assertEmpty($response->toArray());
        } catch (ExceptionInterface $e) {
            self::assertEquals(StatusCode::INTERNAL_SERVER_ERROR->getValue(), $e->getCode());
        }
    }
}
