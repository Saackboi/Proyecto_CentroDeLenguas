<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <title>{{ $title }}</title>
    <style>
      body {
        font-family: Arial, sans-serif;
        font-size: 12px;
        color: #222222;
      }
      h2 {
        margin-bottom: 12px;
      }
      table {
        width: 100%;
        border-collapse: collapse;
      }
      th,
      td {
        border: 1px solid #dddddd;
        padding: 6px;
        text-align: left;
      }
      th {
        background: #f2f2f2;
      }
      .footer {
        margin-top: 12px;
        font-weight: bold;
      }
    </style>
  </head>
  <body>
    <h2>{{ $title }}</h2>
    <table>
      <thead>
        <tr>
          @foreach ($headers as $header)
            <th>{{ $header }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        @foreach ($rows as $index => $row)
          <tr>
            <td>{{ $index + 1 }}</td>
            @foreach ($columns as $column)
              <td>{{ $row->{$column} ?? '' }}</td>
            @endforeach
          </tr>
        @endforeach
      </tbody>
    </table>
    @if (!empty($footer))
      <div class="footer">{{ $footer }}</div>
    @endif
  </body>
</html>
