<?php
/**
 * Created by PhpStorm.
 * User: tamaskovacs
 * Date: 2017. 01. 27.
 * Time: 21:45
 */

namespace Callisto;


use Psr\Http\Message\StreamInterface;

abstract class Psr7Stream implements StreamInterface
{
	/**
	 * Connection resource.
	 *
	 * @var resource
	 */
	protected $connection;


	/**
	 * @throws \Exception
	 */
	public function __toString()
	{
		throw new \RuntimeException('Cannot read the entire stream.');
	}

	/**
	 * Closes the stream and any underlying resources.
	 *
	 * @return void
	 */
	public function close() : void
	{
		fclose($this->connection);
	}

	/**
	 * Separates any underlying resources from the stream.
	 *
	 * After the stream has been detached, the stream is in an unusable state.
	 *
	 * @return resource|null Underlying PHP stream, if any
	 */
	public function detach()
	{
		$this->close();
		return null;
	}

	/**
	 * We do not know the size of the stream.
	 * @return void
	 * @throws \RuntimeException
	 */
	public function getSize() : void
	{
		throw new \RuntimeException('We no not know the size of the stream.');
	}

	/**
	 * We do not have the position of the cursor.
	 *
	 * @return void
	 * @throws \RuntimeException on error.
	 */
	public function tell() : void
	{
		throw new \RuntimeException('We do not know the position of the cursor.');
	}

	/**
	 * Returns true if the stream is at the end of the stream.
	 *
	 * @return bool
	 */
	public function eof() : bool
	{
		return feof($this->connection);
	}

	/**
	 * The stream is not seekable.
	 *
	 * @return bool
	 */
	public function isSeekable() : bool
	{
		return false;
	}

	/**
	 * The stream is not seekable.
	 *
	 * @throws \RuntimeException on failure.
	 */
	public function seek($offset, $whence = SEEK_SET) : void
	{
		throw new \RuntimeException('The stream is not seekable.');
	}

	/**
	 * The stream cannot be rewind.
	 *
	 * @throws \RuntimeException on failure.
	 */
	public function rewind() : void
	{
		throw new \RuntimeException('The stream is not seekable therefore it cannot be rewind.');
	}

	/**
	 * Returns whether or not the stream is writable.
	 *
	 * @return bool
	 */
	public function isWritable() : bool
	{
		return true;
	}

	/**
	 * Write data to the stream.
	 *
	 * @param string $string The string that is to be written.
	 * @return int Returns the number of bytes written to the stream.
	 * @throws \RuntimeException on failure.
	 */
	public function write($string) : int
	{
		$length = strlen($string);
		fwrite($this->connection, $string, $length);

		return $length;
	}

	/**
	 * Returns whether or not the stream is readable.
	 *
	 * @return bool
	 */
	public function isReadable() : bool
	{
		return true;
	}

	/**
	 * Read data from the stream.
	 *
	 * @param int $length Read up to $length bytes from the object and return
	 *     them. Fewer than $length bytes may be returned if underlying stream
	 *     call returns fewer bytes.
	 * @return string Returns the data read from the stream, or an empty string
	 *     if no bytes are available.
	 * @throws \RuntimeException if an error occurs.
	 */
	public function read($length)
	{
		return fread($this->connection, $length);
	}

	/**
	 * Reads the next line fron the stream.
	 *
	 * @param int $length Read up to $length bytes from the object and return
	 *     them. Fewer than $length bytes may be returned if underlying stream
	 *     call returns fewer bytes.
	 * @return string Returns the data read from the stream, or an empty string
	 *     if no bytes are available.
	 * @throws \RuntimeException if an error occurs.
	 */
	public function readLine($length = 1024) : string
	{
		return (string)fgets($this->connection, $length);
	}

	/**
	 * Returns the remaining contents in a string
	 *
	 * @return string
	 * @throws \RuntimeException if unable to read or an error occurs while
	 *     reading.
	 */
	public function getContents()
	{
		throw new \RuntimeException('We cannot return the remaining contents of the stream.');
	}

	/**
	 * Get stream metadata as an associative array or retrieve a specific key.
	 *
	 * The keys returned are identical to the keys returned from PHP's
	 * stream_get_meta_data() function.
	 *
	 * @link http://php.net/manual/en/function.stream-get-meta-data.php
	 * @param string $key Specific metadata to retrieve.
	 * @return array|mixed|null Returns an associative array if no key is
	 *     provided. Returns a specific key value if a key is provided and the
	 *     value is found, or null if the key is not found.
	 */
	public function getMetadata($key = null)
	{
		return stream_get_meta_data($this->connection);
	}
}