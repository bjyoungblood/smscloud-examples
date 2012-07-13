package com.smscloud.client;

public class ClientException extends Exception {
	protected Exception previousException;

	public ClientException(Exception e) {
		this.previousException = e;
	}

	public Exception getPreviousException() {
		return previousException;
	}

	public void setPreviousException(Exception previousException) {
		this.previousException = previousException;
	}
}
