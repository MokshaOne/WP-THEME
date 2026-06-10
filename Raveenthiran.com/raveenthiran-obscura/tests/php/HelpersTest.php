<?php
use PHPUnit\Framework\TestCase;

final class HelpersTest extends TestCase {
	public function test_aes_roundtrip(): void {
		if ( ! function_exists( 'openssl_encrypt' ) ) { $this->markTestSkipped( 'no openssl' ); }
		$blob = nr_encrypt_blob( "name,email\nAda,ada@x.com", 'hunter2' );
		$this->assertNotSame( '', $blob );
		$this->assertSame( "name,email\nAda,ada@x.com", nr_decrypt_blob( $blob, 'hunter2' ) );
		$this->assertSame( '', nr_decrypt_blob( $blob, 'wrong-pass' ) ); // wrong key → empty
	}
	public function test_parse_pipe_lines(): void {
		$rows = nr_parse_pipe_lines( "A | b | c\n\n Solo \nX|Y" );
		$this->assertCount( 3, $rows );
		$this->assertSame( [ 'A', 'b', 'c' ], $rows[0] );
		$this->assertSame( [ 'Solo' ], $rows[1] );
		$this->assertSame( [ 'X', 'Y' ], $rows[2] );
		$this->assertCount( 1, nr_parse_pipe_lines( "a\nb\nc", 1 ) );
	}
	public function test_hex_key(): void {
		$this->assertSame( 'abc123DEF456', nr_is_hex_key( 'abc123DEF456' ) );
		$this->assertSame( '', nr_is_hex_key( 'xyz' ) );          // too short after stripping
		$this->assertSame( 'deadbeef', nr_is_hex_key( 'dead-beef' ) );
	}
}
